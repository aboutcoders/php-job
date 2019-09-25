<?php

namespace Abc\Job\Tests\Interop\Driver;

use Abc\Job\Broker\Config;
use Abc\Job\Broker\Route;
use Abc\Job\Broker\RouteCollection;
use Abc\Job\Interop\Driver\GenericDriver;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Producer;
use Interop\Queue\Queue;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class GenericDriverTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $contextMock;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var GenericDriver
     */
    private $subject;

    public function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->config = new Config('some.prefix', '_', 'default_queue_name', 'default_replyto_name');

        $route_A = new Route('jobNameA', 'queuename', 'replya');

        $this->routeCollection = new RouteCollection([$route_A]);

        $this->subject = $this->getMockForAbstractClass(GenericDriver::class, [
            $this->contextMock,
            $this->config,
            $this->routeCollection,
            new NullLogger(),
        ]);
    }

    public function testSendMessageWithRoute()
    {
        $message = $this->createMock(Message::class);
        $message->expects($this->any())->method('getHeader')->with(Config::NAME)->willReturn('jobNameA');
        $message->expects($this->once())->method('setReplyTo')->with('some.prefix_replya');

        $queue = $this->createMock(Queue::class);
        $producer = $this->createMock(Producer::class);

        $this->contextMock->expects($this->once())->method('createQueue')->with('some.prefix_queuename')->willReturn($queue);
        $this->contextMock->expects($this->once())->method('createProducer')->willReturn($producer);

        $producer->expects($this->once())->method('send')->with($queue, $message);

        $this->subject->sendMessage($message);
    }


    public function testSendMessageWithoutRoute()
    {
        $message = $this->createMock(Message::class);
        $message->expects($this->any())->method('getHeader')->with(Config::NAME)->willReturn('undefined');
        $message->expects($this->once())->method('setReplyTo')->with('some.prefix_default_replyto_name');

        $queue = $this->createMock(Queue::class);
        $producer = $this->createMock(Producer::class);

        $this->contextMock->expects($this->once())->method('createQueue')->with('some.prefix_default_queue_name')->willReturn($queue);
        $this->contextMock->expects($this->once())->method('createProducer')->willReturn($producer);

        $producer->expects($this->once())->method('send')->with($queue, $message);

        $this->subject->sendMessage($message);
    }

    public function testCreateQueue()
    {
        $queue = $this->createMock(Queue::class);

        $this->contextMock->expects($this->once())->method('createQueue')->with('some.prefix_queuename')->willReturn($queue);

        $this->assertSame($queue, $this->subject->createQueue('queueName'));
    }
}
