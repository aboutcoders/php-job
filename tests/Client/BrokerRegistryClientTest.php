<?php

namespace Abc\Job\Tests\Client;

use Abc\Job\Broker\BrokerInterface;
use Abc\Job\Client\BoundBrokerClient;
use Abc\Job\Client\BrokerClient;
use Abc\Job\Client\BrokerRegistryClient;
use PHPUnit\Framework\TestCase;

class BrokerRegistryClientTest extends TestCase
{
    /**
     * @var BrokerClient
     */
    private $brokerClient;

    public function setUp(): void
    {
        $this->brokerClient = $this->createMock(BrokerClient::class);
    }

    public function testExists()
    {
        $this->assertTrue((new BrokerRegistryClient($this->brokerClient))->exists('someId'));
    }

    public function testGet()
    {
        $subject = (new BrokerRegistryClient($this->brokerClient))->get('someId');

        $this->assertInstanceOf(BrokerInterface::class, $subject);
        $this->assertInstanceOf(BoundBrokerClient::class, $subject);
    }
}
