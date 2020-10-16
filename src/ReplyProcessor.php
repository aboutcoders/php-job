<?php

namespace Abc\Job;

use Abc\Job\Model\JobInterface;
use Abc\Job\Processor\Reply;
use Psr\Log\LoggerInterface;

class ReplyProcessor
{
    /**
     * @var JobServer
     */
    private $jobServer;

    /**
     * @var  JobManager
     */
    private $jobManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(JobServer $jobServer, JobManager $jobManager, LoggerInterface $logger)
    {
        $this->jobServer = $jobServer;
        $this->jobManager = $jobManager;
        $this->logger = $logger;
    }

    /**
     * @param Reply $reply
     * @throws NotFoundException
     */
    public function process(Reply $reply): void
    {
        $job = $this->jobManager->find($reply->getJobId());
        if (null == $job) {
            throw new NotFoundException($reply->getJobId());
        }

        $job->setOutput($reply->getOutput());
        $this->updateJob($job, $reply->getStatus(), $reply->getProcessingTime(), $reply->getCreatedTimestamp());

        $this->jobManager->save($job);
    }

    private function updateJob(JobInterface $job, string $status, ?float $processingTime, ?int $timestamp): void
    {
        if (Type::SEQUENCE() == $job->getType()) {
            $children = JobManager::sortByPosition($job->getChildren());
            if (null != $next = JobManager::findNext($children, true)) {
                if (Status::COMPLETE == $status) {
                    $this->jobServer->trigger($next);
                    $status = Status::RUNNING;
                }

                if (Status::FAILED == $status) {
                    $this->cancelSequence($children);
                }
            }
        }

        if (Type::BATCH() == $job->getType() && Status::RUNNING == $job->getStatus() && Status::COMPLETE == $status) {
            foreach ($job->getChildren() as $child) {
                if (Status::RUNNING == $child->getStatus()) {
                    $status = Status::RUNNING;
                    break;
                }
            }
        }

        $job->setStatus($status);
        $job->addProcessingTime($processingTime ?? 0);

        if (in_array($status, [Status::COMPLETE, Status::FAILED])) {
            $job->setCompletedAt(new \DateTime('@'.$timestamp));
        }

        if ($job->hasParent()) {
            if (Status::FAILED != $job->getParent()->getStatus() && Status::FAILED == $job->getStatus(
                ) && $job->isAllowFailure()) {
                $status = Status::COMPLETE;
            }

            $this->updateJob($job->getParent(), $status, $processingTime, $timestamp);
        }
    }

    private function cancel(JobInterface $job): void
    {
        $job->setStatus(Status::CANCELLED);
        foreach ($job->getChildren() as $child) {
            $this->cancel($child);
        }
    }

    /**
     * @param JobInterface[] $orderedChildren
     */
    private function cancelSequence(array $orderedChildren): void
    {
        $cancel = false;
        for ($i = 0; $i < count($orderedChildren); $i++) {
            if ($cancel || Status::FAILED == $orderedChildren[$i]->getStatus()) {
                if (isset($orderedChildren[$i + 1])) {
                    $this->cancel($orderedChildren[$i + 1]);
                    $cancel = true;
                }
            }
        }
    }
}
