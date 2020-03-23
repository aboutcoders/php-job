<?php

namespace Abc\Job\Tests\Controller;

use Abc\Job\Broker\Broker;
use Abc\Job\Broker\BrokerInterface;
use Abc\Job\Broker\RegistryInterface;
use Abc\Job\Controller\BrokerController;
use Abc\Job\Controller\JobController;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;

class BrokerControllerTest extends AbstractControllerTestCase
{
    /**
     * @var RegistryInterface|MockObject
     */
    private $registry;

    /**
     * @var BrokerController
     */
    private $subject;

    public function setUp(): void
    {
        $this->registry = $this->createMock(RegistryInterface::class);
        $this->subject = new BrokerController($this->registry, new NullLogger());
    }

    public function testSetup()
    {
        $broker = $this->createMock(BrokerInterface::class);
        $broker->expects($this->any())->method('getRoutes')->willReturn([$this->createMock(Broker::class)]);

        $this->registry->expects($this->any())->method('exists')->with('someName')->willReturn(true);
        $this->registry->expects($this->any())->method('get')->willReturn($broker);

        $response = $this->subject->setup('someName', 'requestUri');

        $this->assertStatusCode(200, $response);
        $this->assertStdJsonResponseHeader($response);
        $this->assertEquals(json_encode(['name' => 'someName']), (string)$response->getBody());
    }

    public function testSetupWithBrokerNotFound()
    {
        $this->registry->expects($this->any())->method('exists')->with('someName')->willReturn(false);

        $response = $this->subject->setup('someName', 'requestUri');

        $this->assertStatusCode(404, $response);
        $this->assertNotFoundResponse($response, 'Broker', 'someName');
    }

    public function testSetupWithNoRoutes()
    {
        $broker = $this->createMock(BrokerInterface::class);
        $broker->expects($this->any())->method('getRoutes')->willReturn(null);

        $this->registry->expects($this->any())->method('exists')->with('someName')->willReturn(true);
        $this->registry->expects($this->any())->method('get')->willReturn($broker);

        $response = $this->subject->setup('someName', 'requestUri');

        $this->assertStatusCode(409, $response);
        $this->assertProblemJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals(JobController::TYPE_URL . 'conflict', $data['type']);
        $this->assertEquals('Conflict', $data['title']);
        $this->assertEquals(409, $data['status']);
        $this->assertEquals('No routes registered', $data['detail']);
        $this->assertEquals('requestUri', $data['instance']);
    }

    public function testSetupWithServerException()
    {
        $this->registry->expects($this->any())->method('exists')->with('someName')->willReturn(true);
        $this->registry->expects($this->once())->method('get')->willThrowException(new \Exception());

        $response = $this->subject->setup('someName', 'requestUri');

        $this->assertServerErrorResponse($response);
    }
}
