<?php

namespace Abc\Job\Schedule;

use Abc\Job\Job;
use Abc\Job\ServerInterface;
use Abc\Scheduler\ProcessorInterface;
use Abc\Scheduler\ScheduleInterface as BaseScheduleInterface;
use Abc\Job\Model\ScheduleInterface;

class ScheduleProcessor implements ProcessorInterface
{
    /**
     * @var ServerInterface
     */
    private $jobServer;

    /**
     * @param \Abc\Job\ServerInterface $jobServer
     */
    public function __construct(ServerInterface $jobServer)
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
