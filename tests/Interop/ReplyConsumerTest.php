<?php

namespace Abc\Job\Tests\Interop;

use Abc\Job\Interop\ReplyConsumer;
use Abc\Job\NotFoundException;
use Abc\Job\Processor\Reply;
use Abc\Job\ReplyProcessor;
use Abc\Job\Status;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ReplyConsumerTest extends TestCase
{
    /**
     * @var ReplyProcessor|MockObject
     */
    private $replyProcessor;

    /**
     * @var ReplyConsumer
     */
    private $subject;

    public function setUp(): void
    {
        $this->replyProcessor = $this->createMock(ReplyProcessor::class);
        $this->subject = new ReplyConsumer($this->replyProcessor, new NullLogger());
    }

    public function testProcess()
    {
        $message = $this->createMock(Message::class);
        $context = $this->createMock(Context::class);

        $message->expects($this->any())->method('getBody')->willReturn(json_encode((object) [
            'jobId' => 'someJobId',
            'status' => Status::RUNNING,
        ]));

        $message->expects($this->any())->method('getCorrelationId')->willReturn('someJobId');

        $this->replyProcessor->expects($this->once())->method('process')->with($this->equalTo(new Reply('someJobId', Status::RUNNING)));

        $result = $this->subject->process($message, $context);
        $this->assertEquals(Processor::ACK, $result);
    }

    public function testProcessHandlesNotFoundException()
    {
        $message = $this->createMock(Message::class);
        $context = $this->createMock(Context::class);

        $message->expects($this->any())->method('getBody')->willReturn(json_encode((object) [
            'jobId' => 'someJobId',
            'status' => Status::RUNNING,
        ]));

        $message->expects($this->any())->method('getCorrelationId')->willReturn('someJobId');

        $this->replyProcessor->expects($this->once())->method('process')->with( $this->equalTo(new Reply('someJobId', Status::RUNNING)))->willThrowException(new NotFoundException('someJobId'));

        $result = $this->subject->process($message, $context);
        $this->assertEquals(Processor::REJECT, $result);
    }
}
