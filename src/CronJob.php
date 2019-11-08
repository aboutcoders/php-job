<?php

namespace Abc\Job;

interface CronJob
{
    public function getId(): ?string;

    public function getSchedule(): string;

    public function setSchedule(string $expression): void;

    public function getJob(): Job;

    public function setJob(Job $job): void;

    public function getCreatedAt(): ?\DateTime;

    public function getUpdatedAt(): ?\DateTime;
}
