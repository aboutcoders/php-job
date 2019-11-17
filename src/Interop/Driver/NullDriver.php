<?php

namespace Abc\Job\Interop\Driver;

use Psr\Log\LoggerInterface;

class NullDriver extends GenericDriver
{
    public function declareQueue(string $queue, LoggerInterface $logger = null): void
    {
    }
}
