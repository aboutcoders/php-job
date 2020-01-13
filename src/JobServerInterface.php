<?php

namespace Abc\Job;

/**
 * Job API
 */
interface JobServerInterface
{
    /**
     * @param JobFilter $filter
     * @return Result[]
     */
    public function list(JobFilter $filter = null): array;

    /**
     * @param Job $job
     * @return Result
     * @throws NoRouteException
     */
    public function process(Job $job): Result;

    /**
     * @param string $id
     * @return Result|null null if job not found
     */
    public function result(string $id): ?Result;

    /**
     * @param string $id
     * @return Result|null null if job not found
     */
    public function restart(string $id): ?Result;

    /**
     * @param string $id
     * @return bool Whether operation succeeded, null if not found
     */
    public function cancel(string $id): ?bool;

    /**
     * @param string $id
     * @return bool True if successful, null if not found
     */
    public function delete(string $id): ?bool;
}
