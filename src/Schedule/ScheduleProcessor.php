<?php

namespace Abc\Job\Schedule;

use Abc\Job\Job;
use Abc\Job\JobServerInterface;
use Abc\Scheduler\ProcessorInterface;
use Abc\Scheduler\ScheduleInterface as BaseScheduleInterface;
use Abc\Job\Model\ScheduleInterface;

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

    public function process(BaseScheduleInterface $schedule): void
    {
        /** @var ScheduleInterface $schedule */
        if (! $schedule instanceof ScheduleInterface) {
            throw new \InvalidArgumentException(sprintf('Schedule must implement %s', ScheduleInterface::class));
        }

        $job = Job::fromJson($schedule->getJobJson());
        $job->setExternalId($schedule->getId());

        $this->jobServer->process($job);
    }
}
