<?php

namespace Abc\Job\Tests\Interop\Driver;

use Abc\Job\Broker\Route;
use Abc\Job\Broker\RouteCollection;
use Abc\Job\Interop\Driver\AmqpDriver;
use Interop\Amqp\AmqpContext;
use Interop\Amqp\AmqpQueue;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AmqpDriverTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $contextMock;

    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var AmqpDriver|MockObject
     */
    private $subject;

    public function setUp(): void
    {
        $this->contextMock = $this->createMock(AmqpContext::class);

        $route_A = new Route('jobNameA', 'queueA', 'replyToA');
        $route_B = new Route('jobNameB', 'queueB', 'replyToB');

        $this->routeCollection = new RouteCollection([$route_A, $route_B]);

        $this->subject = $this->getMockBuilder(AmqpDriver::class)->setMethodsExcept(['setupBroker'])->disableOriginalConstructor()->getMock();

        $this->subject->expects($this->any())->method('getRouteCollection')->willReturn($this->routeCollection);
        $this->subject->expects($this->any())->method('getContext')->willReturn($this->contextMock);
    }

    public function testSetUpBroker()
    {
        $queues = [
            'queueA' => $this->createMock(AmqpQueue::class),
            'queueB' => $this->createMock(AmqpQueue::class),
            'replyToA' => $this->createMock(AmqpQueue::class),
            'replyToB' => $this->createMock(AmqpQueue::class),
        ];

        $callback = function ($name) use ($queues) {
            return $queues[$name] ?? null;
        };

        $this->subject->expects($this->any())->method('createQueue')->willReturnCallback($callback);

        $this->contextMock->expects($this->exactly(4))->method('declareQueue')->withConsecutive([$queues['queueA']], [$queues['queueB']], [$queues['replyToA']], [$queues['replyToB']]);

        $this->subject->setupBroker();
    }
}
