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

    public function declareQueue(string $queueName, LoggerInterface $logger = null): void
    {
        $logger = $logger ?: new NullLogger();

        $queue = $this->getContext()->createQueue($queueName);

        $this->getContext()->declareQueue($queue);

        $logger->notice(sprintf('[AmqpDriver] Declared queue: %s', $queueName));
    }
}
