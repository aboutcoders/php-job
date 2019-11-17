<?php

namespace Abc\Job\Interop;

use Abc\Job\Broker\RouteRegistryInterface;
use Abc\Job\Interop\Driver\AmqpDriver;
use Abc\Job\Interop\Driver\NullDriver;
use Interop\Queue\Context;
use Psr\Log\LoggerInterface;

class DriverFactory
{
    /**
     * @var RouteRegistryInterface
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
        if ($context instanceof \Interop\Amqp\AmqpContext) {
            return new AmqpDriver($context, $this->routeRegistry, $this->logger);
        }
        if ($context instanceof \Enqueue\Null\NullContext) {
            return new NullDriver($context, $this->routeRegistry, $this->logger);
        }
        else {
            $path = explode('\\',  get_class($context));
            $transport = 'Abc\\Job\\Interop\\Driver\\' . array_pop($path);

            throw new \LogicException(sprintf('The transport "%s" is not supported (yet). Please file a feature request or become a contributor.', $transport));
        }
    }
}
