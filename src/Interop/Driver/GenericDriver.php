<?php

namespace Abc\Job\Interop\Driver;

use Abc\Job\Broker\Config;
use Abc\Job\Broker\RouteRegistryInterface;
use Abc\Job\Interop\DriverInterface;
use Abc\Job\NoRouteException;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Psr\Log\LoggerInterface;

abstract class GenericDriver implements DriverInterface
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var RouteRegistryInterface
     */
    private $routeRegistry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Context $context, RouteRegistryInterface $routeRegistry, LoggerInterface $logger)
    {
        $this->context = $context;
        $this->routeRegistry = $routeRegistry;
        $this->logger = $logger;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @throws \InvalidArgumentException If job name not set in message header
     * @throws NoRouteException If no route is defined for the job
     */
    public function sendMessage(Message $message): void
    {
        $jobName = $message->getProperty(Config::NAME, false);
        if (false === $jobName) {
            throw new \InvalidArgumentException(sprintf('The job name must be set in the message header "%s"', Config::NAME));
        }

        $route = $this->routeRegistry->get($jobName);
        if (null == $route) {
            throw new NoRouteException($jobName);
        }

        $destination = $this->context->createQueue($route->getQueue());

        $message->setReplyTo($route->getReplyTo());

        $this->logger->debug(sprintf('[%s] Send message for job "%s" to queue "%s" with replyTo %s', static::class, $message->getCorrelationId(), $destination->getQueueName(), $message->getReplyTo()));

        $this->getContext()->createProducer()->send($destination, $message);
    }
}
