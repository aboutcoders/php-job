<?php

namespace Abc\Job\Model;

use Abc\Job\Broker\Route;

interface RouteManagerInterface
{
    /**
     * @return Route[]
     */
    public function all(): array;

    public function find(string $jobName): ?Route;

    public function save(Route $route): void;
}
