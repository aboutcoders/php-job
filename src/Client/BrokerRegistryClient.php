<?php

namespace Abc\Job\Client;

use Abc\Job\Broker\BrokerInterface;
use Abc\Job\Broker\RegistryInterface;

class BrokerRegistryClient implements RegistryInterface
{
    /**
     * @var BrokerClient
     */
    private $brokerClient;

    public function __construct(BrokerClient $brokerClient)
    {
        $this->brokerClient = $brokerClient;
    }

    public function exists(string $name): bool
    {
        // fixme: not ideal
        return true;
    }

    public function get(string $name): ?BrokerInterface
    {
        return new BoundBrokerClient($name, $this->brokerClient);
    }
}