<?php

namespace Abc\Job\Model;

use Abc\Job\CronJobFilter;
use Abc\Job\OrderBy;

interface CronJobManagerInterface
{
    public function create(string $scheduleExpression, \Abc\Job\Job $job): CronJobInterface;

    public function getClass(): string;

    public function delete(CronJobInterface $job): void;

    public function save(CronJobInterface $job): void;

    public function find(string $id): ?CronJobInterface;

    /**
     * @param CronJobFilter|null $filter
     * @param OrderBy|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return CronJobInterface[]
     */
    public function findBy(
        CronJobFilter $filter = null,
        OrderBy $orderBy = null,
        int $limit = null,
        int $offset = null
    ): array;
}
