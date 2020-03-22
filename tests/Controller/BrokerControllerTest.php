<?php

namespace Abc\Job\Tests\Controller;

use Abc\Job\Broker\BrokerInterface;
use Abc\Job\Broker\RegistryInterface;
use Abc\Job\Controller\BrokerController;
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

        $this->registry->expects($this->any())->method('exists')->with('seomName')->willReturn(true);
        $this->registry->expects($this->any())->method('get')->willReturn($broker);

        $response = $this->subject->setup('seomName', 'requestUri');

        $this->assertStatusCode(200, $response);
        $this->assertStdJsonResponseHeader($response);
        $this->assertEquals(json_encode(['name' => 'someName']), (string)$response->getBody());
    }

    public function testSetupWithBrokerNotFound()
    {
        $this->registry->expects($this->any())->method('exists')->with('seomName')->willReturn(false);

        $response = $this->subject->setup('seomName', 'requestUri');

        $this->assertStatusCode(404, $response);
        $this->assertNotFoundResponse($response, 'Broker', 'seomName');
    }

    public function testSetupWithServerException()
    {
        $this->registry->expects($this->any())->method('exists')->with('seomName')->willReturn(true);
        $this->registry->expects($this->once())->method('get')->willThrowException(new \Exception());

        $response = $this->subject->setup('seomName', 'requestUri');

        $this->assertServerErrorResponse($response);
    }
}
