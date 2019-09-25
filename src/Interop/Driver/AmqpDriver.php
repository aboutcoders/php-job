<?php

namespace Abc\Job\Interop\Driver;

use Interop\Amqp\AmqpContext;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @method AmqpContext getContext
 */
class AmqpDriver extends GenericDriver
{
    public function __construct(AmqpContext $context, ...$args)
    {
        parent::__construct($context, ...$args);
    }

    public function setupBroker(LoggerInterface $logger = null): void
    {
        $logger = $logger ?: new NullLogger();
        $log = function ($text, ...$args) use ($logger) {
            $logger->debug(sprintf('[AmqpDriver] '.$text, ...$args));
        };

        $queues = [];
        $replyQueues = [];
        foreach ($this->getRouteCollection()->all() as $route) {
            $queues[] = $route->getQueueName();
            $replyQueues[] = $route->getReplyTo();
        }

        foreach (array_unique($queues) as $queueName) {
            $queue = $this->createQueue($queueName);
            $log('Declare queue: %s', $queue->getQueueName());
            $this->getContext()->declareQueue($queue);
        }

        foreach (array_unique($replyQueues) as $queueName) {
            $queue = $this->createQueue($queueName);
            $log('Declare reply queue: %s', $queue->getQueueName());
            $this->getContext()->declareQueue($queue);
        }
    }
}
