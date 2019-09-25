<?php

namespace Abc\Job\Model;

interface ScheduleManagerInterface
{
    public function create(string $scheduleExpression, string $jobJson): ScheduleInterface;

    public function delete(ScheduleInterface $job): void;

    public function refresh(ScheduleInterface $job): void;

    public function save(ScheduleInterface $job): void;

    public function find(string $id): ?ScheduleInterface;

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);
}
