<?php

namespace Abc\Job\Interop\Driver;

use Abc\Job\Broker\Config;
use Abc\Job\Broker\RouteCollection;
use Abc\Job\Interop\DriverInterface;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Queue;
use Psr\Log\LoggerInterface;

abstract class GenericDriver implements DriverInterface
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        Config $config,
        RouteCollection $routeCollection,
        LoggerInterface $logger
    ) {
        $this->context = $context;
        $this->config = $config;
        $this->routeCollection = $routeCollection;
        $this->logger = $logger;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getRouteCollection(): RouteCollection
    {
        return $this->routeCollection;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function sendMessage(Message $message): void
    {
        $jobName = $message->getHeader(Config::NAME, false);
        if (false === $jobName) {
            throw new \InvalidArgumentException('The job name must be set in the message header "%s"', Config::NAME);
        }

        $route = $this->routeCollection->get($jobName);
        if(null == $route)
        {
            $destination = $this->createQueue($this->getConfig()->getDefaultQueue());
            $replyTo = $this->getConfig()->getDefaultReplyTo();
        }
        else{
            $destination = $this->createQueue($route->getQueueName());
            $replyTo = $route->getReplyTo();
        }

        $message->setReplyTo($this->createTransportQueueName($replyTo, true));

        $this->logger->debug(sprintf('[%s] Send message for job "%s" to queue "%s" with replyTo %s', static::class, $message->getCorrelationId(), $destination->getQueueName(), $message->getReplyTo()));

        $this->getContext()->createProducer()->send($destination, $message);
    }

    public function createQueue(string $clientQueueName, bool $prefix = true): Queue
    {
        $transportName = $this->createTransportQueueName($clientQueueName, $prefix);

        return $this->doCreateQueue($transportName);
    }

    protected function createTransportQueueName(string $name, bool $prefix): string
    {
        $clientPrefix = $prefix ? $this->config->getPrefix() : '';

        return strtolower(implode($this->config->getSeparator(), array_filter([$clientPrefix, $name])));
    }

    protected function doCreateQueue(string $transportQueueName): Queue
    {
        return $this->context->createQueue($transportQueueName);
    }
}
