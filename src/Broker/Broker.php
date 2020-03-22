<?php

namespace Abc\Job\Broker;

use Abc\Job\Interop\DriverInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Broker implements BrokerInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var RouteRegistryInterface
     */
    private $routes;

    public function __construct(string $name, DriverInterface $driver, RouteRegistryInterface $routes)
    {
        $this->name = $name;
        $this->driver = $driver;
        $this->routes = $routes;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRoutes(): ?array
    {
        return $this->routes->all();
    }

    public function setup(LoggerInterface $logger = null): void
    {
        if (empty($this->routes->all())) {
            throw new \LogicException('Failed to setup since no routes are registered');
        }

        $logger = $logger ?: new NullLogger();
        $declaredQueues = [];
        foreach ($this->routes->all() as $route) {
            $this->declareQueueOnce($route->getQueue(), $declaredQueues, $logger);
            $this->declareQueueOnce($route->getReplyTo(), $declaredQueues, $logger);
        }
    }

    private function declareQueueOnce(string $queueName, array &$declaredQueues, LoggerInterface $logger)
    {
        if (!in_array($queueName, $declaredQueues)) {
            $this->driver->declareQueue($queueName);
            $logger->info(sprintf('Declared queue "%s" at broker "%s"', $queueName, $this->name));
            $declaredQueues[] = $queueName;
        }
    }
}
