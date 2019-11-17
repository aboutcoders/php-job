<?php

namespace Abc\Job\Broker;

interface RouteRegistryInterface
{
    /**
     * @return Route[]
     */
    public function all(): array;

    public function get(string $jobName): ?Route;

    public function add(Route $route): void;
}
