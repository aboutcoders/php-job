<?php

namespace Abc\Job\Broker;

use Abc\Job\Interop\DriverInterface;
use Psr\Log\LoggerInterface;

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

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(string $name, DriverInterface $driver, RouteRegistryInterface $routes, LoggerInterface $logger)
    {
        $this->name = $name;
        $this->driver = $driver;
        $this->routes = $routes;
        $this->logger = $logger;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setup(LoggerInterface $logger = null): void
    {
        $declaredQueues = [];
        foreach ($this->routes->all() as $route) {
            $this->declareQueueOnce($route->getQueue(), $declaredQueues, $logger ?? $this->logger);
            $this->declareQueueOnce($route->getReplyTo(), $declaredQueues, $logger ?? $this->logger);
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
