<?php

namespace Abc\Job\Interop;

use Interop\Queue\Context;
use Interop\Queue\Message;
use Psr\Log\LoggerInterface;

/**
 * Provides vendor specific logic of a broker.
 */
interface DriverInterface
{
    public function getContext(): Context;

    /**
     * Creates all required queues, exchanges, topics, bindings etc.
     */
    public function setupBroker(LoggerInterface $logger = null): void;

    public function sendMessage(Message $message): void;
}
