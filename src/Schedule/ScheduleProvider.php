<?php

namespace Abc\Job\Schedule;

use Abc\Job\Model\ScheduleManagerInterface;
use Abc\Job\Model\ScheduleInterface;
use Abc\Scheduler\ProviderInterface;
use Abc\Scheduler\ScheduleInterface as BaseScheduleInterface;

class ScheduleProvider implements ProviderInterface
{
    /**
     * @var ScheduleManagerInterface
     */
    private $scheduleManager;

    public function __construct(ScheduleManagerInterface $manager)
    {
        $this->scheduleManager = $manager;
    }

    public function getName(): string
    {
        return 'abc_job';
    }

    public function provideSchedules(int $limit = null, int $offset = null): array
    {
        return $this->scheduleManager->findBy([], [], $limit, $offset);
    }

    public function save(BaseScheduleInterface $schedule): void
    {
        /** @var ScheduleInterface $schedule */
        if(!$schedule instanceof ScheduleInterface)
        {
            throw new \InvalidArgumentException(sprintf('Schedule must implement %s', ScheduleInterface::class));
        }

        $this->scheduleManager->save($schedule);
    }
}
