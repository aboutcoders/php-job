<?php

namespace Abc\Job\Model;

interface RouteRegistryInterface
{
    /**
     * @return RouteInterface[]
     */
    public function all(): array;

    public function get(string $jobName): ?RouteInterface;

    public function add(RouteInterface $route): void;
}
