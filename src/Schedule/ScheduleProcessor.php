<?php

namespace Abc\Job\Schedule;

use Abc\Job\JobServerInterface;
use Abc\Scheduler\ProcessorInterface;
use Abc\Scheduler\ScheduleInterface as BaseScheduleInterface;
use Abc\Job\Model\CronJobInterface;

class ScheduleProcessor implements ProcessorInterface
{
    /**
     * @var JobServerInterface
     */
    private $jobServer;

    /**
     * @param \Abc\Job\JobServerInterface $jobServer
     */
    public function __construct(JobServerInterface $jobServer)
    {
        $this->jobServer = $jobServer;
    }

    public function process(BaseScheduleInterface $cronJob): void
    {
        /** @var CronJobInterface $cronJob */
        if (! $cronJob instanceof CronJobInterface) {
            throw new \InvalidArgumentException(sprintf('$cronJob must implement %s', CronJobInterface::class));
        }

        $job = $cronJob->getJob();
        $job->setExternalId($cronJob->getId());

        $this->jobServer->process($job);
    }
}
