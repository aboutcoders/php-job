<?php

namespace Abc\Job\Schedule;

use Abc\Job\CronJobManager;
use Abc\Job\Model\CronJobInterface;
use Abc\Job\Model\JobManagerInterface;
use Abc\Scheduler\ProviderInterface;
use Abc\Scheduler\ScheduleInterface as BaseScheduleInterface;

class ScheduleProvider implements ProviderInterface
{
    /**
     * @var CronJobManager
     */
    private $cronJobManager;

    /**
     * @var JobManagerInterface
     */
    private $jobManager;

    public function __construct(CronJobManager $cronJobManager, JobManagerInterface $jobManager)
    {
        $this->cronJobManager = $cronJobManager;
        $this->jobManager = $jobManager;
    }

    public function getName(): string
    {
        return 'abc_job';
    }

    public function provideSchedules(int $limit = null, int $offset = null): array
    {
        return $this->cronJobManager->list(null, null, $limit, $offset);
    }

    public function existsConcurrent(BaseScheduleInterface $schedule): bool
    {
        $this->assertScheduleType($schedule);

        /** @var CronJobInterface $schedule */
        return $this->jobManager->existsConcurrent($schedule->getJob());
    }

    public function save(BaseScheduleInterface $schedule): void
    {
        $this->assertScheduleType($schedule);

        /** @var CronJobInterface $schedule */
        $this->cronJobManager->update($schedule);
    }

    private function assertScheduleType(BaseScheduleInterface $schedule): void
    {

        if (!$schedule instanceof CronJobInterface) {
            throw new \InvalidArgumentException(sprintf('Schedule must implement %s', CronJobInterface::class));
        }
    }
}
