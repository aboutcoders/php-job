<?php

namespace Abc\Job\Schedule;

use Abc\Job\CronJobManager;
use Abc\Job\Model\CronJobInterface;
use Abc\Scheduler\ProviderInterface;
use Abc\Scheduler\ScheduleInterface as BaseScheduleInterface;

class ScheduleProvider implements ProviderInterface
{
    /**
     * @var CronJobManager
     */
    private $cronJob;

    public function __construct(CronJobManager $manager)
    {
        $this->cronJob = $manager;
    }

    public function getName(): string
    {
        return 'abc_job';
    }

    public function provideSchedules(int $limit = null, int $offset = null): array
    {
        return $this->cronJob->list(null, null, $limit, $offset);
    }

    public function save(BaseScheduleInterface $schedule): void
    {
        /** @var CronJobInterface $schedule */
        if(!$schedule instanceof CronJobInterface)
        {
            throw new \InvalidArgumentException(sprintf('Schedule must implement %s', CronJobInterface::class));
        }

        $this->cronJob->update($schedule);
    }
}
