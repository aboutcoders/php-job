<?php

namespace Abc\Job\Broker;

use Abc\Job\Model\JobInterface;

/**
 * Creates a message for a job and sends it to a broker
 */
interface ProducerInterface
{
    public function sendMessage(JobInterface $job): void;
}
