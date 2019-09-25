<?php

namespace Abc\Job\Interop;

use Abc\Job\Broker\Config;
use Abc\Job\Broker\ProducerInterface;
use Abc\Job\Model\JobInterface;
use Ramsey\Uuid\Uuid;

class Producer implements ProducerInterface
{
    /**
     * @var DriverInterface
     */
    private $driver;

    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function sendMessage(JobInterface $job): void
    {
        $message = $this->driver->getContext()->createMessage($job->getInput() ?: '');
        $message->setMessageId(Uuid::uuid4());
        $message->setTimestamp(time());
        $message->setCorrelationId($job->getId());
        $message->setHeader(Config::NAME, $job->getName());

        $this->driver->sendMessage($message);
    }
}
