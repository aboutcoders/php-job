<?php

namespace Abc\Job\Model;

use Abc\Job\Job;
use Abc\Job\Filter;

interface JobManagerInterface
{
    public function create(Job $job): JobInterface;

    public function delete(JobInterface $job): void;

    public function refresh(JobInterface $job): void;

    public function save(JobInterface $job): void;

    public function find(string $id): ?JobInterface;

    public function findBy(Filter $filter = null) : array;

    public static function fromArray(JobInterface $job, array $data): JobInterface;
}
