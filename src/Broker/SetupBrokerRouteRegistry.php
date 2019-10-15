<?php

namespace Abc\Job\Broker;

use Abc\Job\Interop\DriverInterface;

class SetupBrokerRouteRegistry implements RouteRegistryInterface
{
    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var RouteRegistryInterface
     */
    private $registry;

    public function __construct(DriverInterface $driver, RouteRegistryInterface $registry)
    {
        $this->driver = $driver;
        $this->registry = $registry;
    }

    public function all(): array
    {
        return $this->registry->all();
    }

    public function get(string $jobName): ?Route
    {
        return $this->registry->get($jobName);
    }

    public function add(Route $route): void
    {
        $this->registry->add($route);

        $this->driver->declareQueue($route->getQueue());
        $this->driver->declareQueue($route->getReplyTo());
    }
}
