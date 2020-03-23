<?php

namespace Abc\Job\Tests\Client;

use Abc\ApiProblem\ApiProblem;
use Abc\ApiProblem\ApiProblemException;
use Abc\Job\Client\BoundBrokerClient;
use Abc\Job\Client\BrokerClient;
use PHPUnit\Framework\MockObject\MockObject;

class BoundBrokerClientTest extends AbstractClientTestCase
{
    /**
     * @var BrokerClient|MockObject
     */
    private $brokerClient;

    /**
     * @var BoundBrokerClient
     */
    private $subject;

    public function setUp(): void
    {
        $this->brokerClient = $this->createMock(BrokerClient::class);

        $this->subject = new BoundBrokerClient('someName', $this->brokerClient);
    }

    public function testSetup()
    {
        $this->brokerClient->expects($this->once())->method('setup')->with('someName');

        $this->subject->setup();
    }

    public function testSetupApiProblemException()
    {
        $this->brokerClient->expects($this->once())->method('setup')->with('someName')->willThrowException(
            new ApiProblemException(
                new ApiProblem('type', 'title', 400, 'detail', 'instance')
            )
        );

        $this->expectException(ApiProblemException::class);

        $this->subject->setup();
    }
}
