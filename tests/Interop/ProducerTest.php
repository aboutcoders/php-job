<?php

namespace Abc\Job\Tests\Interop;

use Abc\Job\Broker\Config;
use Abc\Job\Interop\DriverInterface;
use Abc\Job\Interop\Producer;
use Abc\Job\Model\Job;
use Interop\Amqp\Impl\AmqpMessage;
use Interop\Queue\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProducerTest extends TestCase
{
    /**
     * @var DriverInterface|MockObject
     */
    private $driverMock;

    /**
     * @var MockObject
     */
    private $contextMock;

    public function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);

        $this->driverMock = $this->createMock(DriverInterface::class);
        $this->driverMock->expects($this->any())->method('getContext')->willReturn($this->contextMock);
    }

    public function testSendMessage($input = null)
    {
        $subject = new Producer($this->driverMock);

        $job = new Job();
        $job->setId('jobId');
        $job->setName('joName');
        $job->setInput($input);

        $message = new AmqpMessage();

        $this->contextMock->expects($this->once())->method('createMessage')->willReturn($message);
        $this->driverMock->expects($this->once())->method('sendMessage')->with($this->equalTo($message));

        $subject->sendMessage($job);

        $this->assertNotEmpty($message->getMessageId());
        $this->assertLessThanOrEqual(time(), $message->getTimestamp());
        $this->assertEquals($job->getId(), $message->getCorrelationId());
        $this->assertEquals($job->getName(), $message->getHeader(Config::NAME));
    }
}
