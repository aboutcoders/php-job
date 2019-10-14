<?php

namespace Abc\Job\Interop;

use Abc\Job\Broker\Config;
use Abc\Job\Broker\RouteRegistry;
use Abc\Job\Broker\RouteRegistryInterface;
use Abc\Job\Interop\Driver\AmqpDriver;
use Interop\Amqp\AmqpContext;
use Interop\Queue\Context;
use Psr\Log\LoggerInterface;

class DriverFactory
{
    /**
     * @var RouteRegistry
     */
    private $routeRegistry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(RouteRegistryInterface $routeRegistry, LoggerInterface $logger)
    {
        $this->routeRegistry = $routeRegistry;
        $this->logger = $logger;
    }

    public function create(Context $context): DriverInterface
    {
        if ($context instanceof AmqpContext) {
            return new AmqpDriver($context, $this->routeRegistry, $this->logger);
        } else {
            throw new \LogicException(sprintf('The transport "%s" is not supported (yet). Please file a feature request or become a contributor.', get_class($context)));
        }
    }
}
