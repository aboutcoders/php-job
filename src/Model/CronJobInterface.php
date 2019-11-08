<?php

namespace Abc\Job\Model;

use Abc\Scheduler\ScheduleInterface;

interface CronJobInterface extends \Abc\Job\CronJob, ScheduleInterface
{
    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getJobJson(): ?string;

    public function setJobJson(?string $json): void;
}
