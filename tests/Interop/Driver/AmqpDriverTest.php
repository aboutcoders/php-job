<?php

namespace Abc\Job\Tests\Interop\Driver;

use Abc\Job\Broker\ManagedRouteRegistry;
use Abc\Job\Interop\Driver\AmqpDriver;
use Interop\Amqp\AmqpContext;
use Interop\Amqp\AmqpQueue;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class AmqpDriverTest extends TestCase
{
    /**
     * @var AmqpContext, MockObject
     */
    private $contextMock;

    /**
     * @var ManagedRouteRegistry|MockObject
     */
    private $registryMock;

    /**
     * @var AmqpDriver|MockObject
     */
    private $subject;

    public function setUp(): void
    {
        $this->contextMock = $this->createMock(AmqpContext::class);
        $this->registryMock = $this->createMock(ManagedRouteRegistry::class);
        $this->subject = new AmqpDriver($this->contextMock, $this->registryMock, new NullLogger());
    }

    public function testDeclareQueue()
    {
        $queue = $this->createMock(AmqpQueue::class);

        $this->contextMock->expects($this->once())->method('createQueue')->with('someQueue')->willReturn($queue);

        $this->contextMock->expects($this->once())->method('declareQueue')->with($queue);

        $this->subject->declareQueue('someQueue');
    }
}
