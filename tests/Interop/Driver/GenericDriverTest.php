<?php

namespace Abc\Job\Tests\Interop\Driver;

use Abc\Job\Broker\Config;
use Abc\Job\Broker\Route;
use Abc\Job\Broker\ManagedRouteRegistry;
use Abc\Job\Interop\Driver\GenericDriver;
use Abc\Job\NoRouteException;
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
     * @var ManagedRouteRegistry
     */
    private $routeRegistry;

    /**
     * @var GenericDriver
     */
    private $subject;

    public function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->routeRegistry = $this->createMock(ManagedRouteRegistry::class);

        $this->subject = $this->getMockForAbstractClass(GenericDriver::class, [
            $this->contextMock,
            $this->routeRegistry,
            new NullLogger(),
        ]);
    }

    public function testSendMessageWithRoute()
    {
        $route = new Route('jobName', 'queueName', 'replyTo');

        $this->routeRegistry->expects($this->once())->method('get')->with('jobName')->willReturn($route);

        $message = $this->createMock(Message::class);
        $message->expects($this->atLeastOnce())->method('getProperty')->with(Config::NAME, false)->willReturn('jobName');
        $message->expects($this->once())->method('setReplyTo')->with('replyTo');

        $queue = $this->createMock(Queue::class);
        $producer = $this->createMock(Producer::class);

        $this->contextMock->expects($this->once())->method('createQueue')->with('queueName')->willReturn($queue);
        $this->contextMock->expects($this->once())->method('createProducer')->willReturn($producer);

        $producer->expects($this->once())->method('send')->with($queue, $message);

        $this->subject->sendMessage($message);
    }

    public function testSendMessageWithoutRoute()
    {
        $message = $this->createMock(Message::class);
        $message->expects($this->atLeastOnce())->method('getProperty')->with(Config::NAME, false)->willReturn('undefined');

        $this->routeRegistry->expects($this->once())->method('get')->with('undefined')->willReturn(null);

        $this->expectException(NoRouteException::class);

        $this->subject->sendMessage($message);
    }

    public function testSendMessageWithMissingHeader()
    {
        $message = $this->createMock(Message::class);

        $message->expects($this->atLeastOnce())->method('getProperty')->with(Config::NAME, false)->willReturn(false);

        $this->expectException(\InvalidArgumentException::class);

        $this->subject->sendMessage($message);
    }
}
