<?php

namespace Abc\Job;

use Abc\Job\Broker\ProducerInterface;
use Abc\Job\Doctrine\JobManager;
use Abc\Job\Model\JobInterface;
use Abc\Job\Model\JobManagerInterface;
use Psr\Log\LoggerInterface;

class JobServer implements JobServerInterface
{
    /**
     * @var  ProducerInterface
     */
    private $producer;

    /**
     * @var JobManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ProducerInterface $producer, JobManagerInterface $jobManager, LoggerInterface $logger)
    {
        $this->producer = $producer;
        $this->entityManager = $jobManager;
        $this->logger = $logger;
    }

    public function list(JobFilter $filter = null): array
    {
        $results = [];
        foreach ($this->entityManager->findBy($filter) as $job) {
            $results[] = new Result($job);
        }

        return $results;
    }

    public function process(Job $job): Result
    {
        $managedJob = $this->entityManager->create($job);

        $this->entityManager->save($managedJob);

        $this->logger->info(
            sprintf(
                '[JobServer] Process %s %s with input %s',
                $managedJob->getType(),
                $managedJob->getId(),
                $managedJob->getInput()
            )
        );

        $this->schedule($managedJob);

        $this->entityManager->save($managedJob);

        return new Result($managedJob);
    }

    /**
     * @param string $id
     * @return Result
     */
    public function restart(string $id): ?Result
    {
        $job = $this->entityManager->find($id);
        if (null == $job) {
            return null;
        }

        $this->logger->info(sprintf('[JobServer] Restart %s %s', $job->getType(), $job->getId()));

        $this->restartJob($job);

        $this->schedule($job);

        $this->entityManager->save($job);

        return new Result($job);
    }

    public function cancel(string $id): ?bool
    {
        $job = $this->entityManager->find($id);
        if (null == $job) {
            return null;
        }

        if (Status::WAITING != $job->getStatus() && Type::SEQUENCE() != $job->getType()) {
            return false;
        }

        if (Status::WAITING == $job->getStatus()) {
            $this->doCancel($job);

            $success = true;
        } else {
            $success = false;
            $children = $job->getChildren();
            do {
                $next = JobManager::findNext($children);
                if (null != $next) {
                    $next->setStatus(Status::CANCELLED);
                    $success = true;
                }
            } while ($next != null);
        }

        $this->entityManager->save($job);

        return $success;
    }

    public function result(string $id): ?Result
    {
        $job = $this->entityManager->find($id);
        if (null == $job) {
            return null;
        }

        return new Result($job);
    }

    public function delete(string $id): ?bool
    {
        $job = $this->entityManager->find($id);
        if (null == $job) {
            return null;
        }

        $this->entityManager->delete($job);

        return true;
    }

    public function trigger(JobInterface $job): Result
    {
        $this->logger->info(sprintf('[JobServer] Trigger %s %s', $job->getType(), $job->getId()));

        $this->schedule($job);
        $this->entityManager->save($job);

        return new Result($job);
    }

    private function restartJob(JobInterface $job): void
    {
        $this->logger->debug(sprintf('[JobServer] Reset %s %s', $job->getType(), $job->getId()));

        $job->setStatus(status::WAITING);
        $job->setProcessingTime(0.0);
        $job->setOutput(null);
        $job->setCompletedAt(null);
        $job->setRestarts($job->getRestarts() + 1);

        foreach ($job->getChildren() as $child) {
            $this->restartJob($child);
        }
    }

    private function schedule(JobInterface $job): void
    {
        $this->logger->debug(sprintf('[Scheduler] Schedule job %s', $job->getId()));

        $job->setStatus(Status::SCHEDULED);
        switch ($job->getType()) {
            case Type::JOB():

                $this->producer->sendMessage($job);
                break;
            case Type::BATCH():
                foreach ($job->getChildren() as $child) {
                    $this->schedule($child);
                }
                break;
            case Type::SEQUENCE():
                $this->schedule(JobManager::findNext($job->getChildren()));
                break;
        }
    }

    private function doCancel(JobInterface $job): void
    {
        $job->setStatus(Status::CANCELLED);
        foreach ($job->getChildren() as $child) {
            $this->doCancel($child);
        }
    }
}
