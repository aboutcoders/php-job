<?php

namespace Abc\Job\Model;

use Abc\Scheduler\ScheduleInterface as BaseScheduleInterface;
use DateTime;

interface ScheduleInterface extends BaseScheduleInterface
{
    public function getId(): ?string;

    public function setSchedule(string $expression);

    public function getJobJson(): ?string;

    public function setJobJson(?string $jobJson);

    public function getCreatedAt(): ?DateTime;

    public function setCreatedAt(DateTime $createdAt);

    public function getUpdatedAt(): ?DateTime;

    public function setUpdatedAt(DateTime $updatedAt);
}
