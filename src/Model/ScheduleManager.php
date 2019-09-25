<?php

namespace Abc\Job\Model;

abstract class ScheduleManager implements ScheduleManagerInterface
{
    abstract protected function getClass(): string;

    public function create(string $scheduleExpression, string $jobJson): ScheduleInterface
    {
        $cls = $this->getClass();

        /** @var ScheduleInterface $schedule */
        $schedule = new $cls();
        $schedule->setSchedule($scheduleExpression);
        $schedule->setJobJson($jobJson);

        return $schedule;
    }
}
