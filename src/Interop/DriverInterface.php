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

    public function sendMessage(Message $message): void;

    public function declareQueue(string $queue, LoggerInterface $logger = null): void;
}
