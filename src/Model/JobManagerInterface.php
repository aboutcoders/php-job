<?php

namespace Abc\Job\Model;

use Abc\Job\Job;
use Abc\Job\JobFilter;

interface JobManagerInterface
{
    public function create(Job $job): JobInterface;

    public function delete(JobInterface $job): void;

    public function deleteAll(): int;

    public function refresh(JobInterface $job): void;

    public function save(JobInterface $job): void;

    public function find(string $id): ?JobInterface;

    public function findBy(JobFilter $filter = null) : array;

    public static function fromArray(JobInterface $job, array $data): JobInterface;

    /**
     * Indicates whether an equal job exists that is currently waiting, scheduled or running.
     *
     * @param  Job  $job
     * @return bool
     */
    public function existsConcurrent(Job $job): bool;
}
