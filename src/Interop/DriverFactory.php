<?php

namespace Abc\Job\Interop;

use Abc\Job\Broker\Config;
use Abc\Job\Broker\RouteRegistry;
use Abc\Job\Interop\Driver\AmqpDriver;
use Interop\Amqp\AmqpContext;
use Interop\Queue\Context;
use Psr\Log\LoggerInterface;

class DriverFactory
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var RouteRegistry
     */
    private $routeCollection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Config $config, RouteRegistry $routeCollection, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->routeCollection = $routeCollection;
        $this->logger = $logger;
    }

    public function create(Context $context): DriverInterface
    {
        if ($context instanceof AmqpContext) {
            return new AmqpDriver($context, $this->config, $this->routeCollection, $this->logger);
        } else {
            throw new \LogicException(sprintf('The transport "%s" is not supported (yet). Please file a feature request or become a contributor.', get_class($context)));
        }
    }
}
