<?php

namespace Abc\Job\Tests\Interop;

use Abc\Job\Broker\Config;
use Abc\Job\Interop\JobConsumer;
use Abc\Job\Processor\ProcessorInterface;
use Abc\Job\Processor\ProcessorRegistry;
use Abc\Job\Processor\Result;
use Abc\Job\Status;
use Doctrine\Common\Collections\ArrayCollection;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Interop\Queue\Producer;
use Interop\Queue\Queue;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;

class JobConsumerTest extends TestCase
{
    public static $stackCounter = 0;

    /**
     * @var ProcessorRegistry|MockObject
     */
    private $registry;

    /**
     * @var JobConsumer
     */
    private $subject;

    public function setUp(): void
    {
        $this->registry = $this->createMock(ProcessorRegistry::class);
        $this->subject = new JobConsumer($this->registry, new NullLogger());
    }

    /**
     * @test
     */
    public function rejectsMessageIfCorrelationIdIsNull()
    {
        $this->assertEquals(Processor::REJECT, $this->subject->process($this->createMock(Message::class), $this->createMock(Context::class)));
    }

    /**
     * @test
     */
    public function rejectsMessageIfHeaderIsInvalid()
    {
        $message = $this->createMock(Message::class);
        $message->expects($this->any())->method('getCorrelationId')->willReturn('someId');
        $message->expects($this->atLeastOnce())->method('getHeader')->with(Config::NAME, false)->willReturn(false);

        $this->assertEquals(Processor::REJECT, $this->subject->process($message, $this->createMock(Context::class)));
    }

    /**
     * @test
     */
    public function requeuesMessageIfNoProcessorExists()
    {
        $message = $this->createMock(Message::class);
        $message->expects($this->any())->method('getCorrelationId')->willReturn('someId');
        $message->expects($this->atLeastOnce())->method('getHeader')->with(Config::NAME, false)->willReturn('jobName');

        $this->registry->expects($this->atLeastOnce())->method('get')->with('jobName')->willReturn(null);

        $this->assertEquals(Processor::REQUEUE, $this->subject->process($message, $this->createMock(Context::class)));
    }

    /**
     * @test
     */
    public function requeuesMessageIfJobIsNotWhitelisted()
    {
        $message = $this->createMock(Message::class);
        $message->expects($this->any())->method('getCorrelationId')->willReturn('someId');
        $message->expects($this->atLeastOnce())->method('getHeader')->with(Config::NAME, false)->willReturn('jobName');

        $processor = $this->createMock(ProcessorInterface::class);

        $this->registry->expects($this->atLeastOnce())->method('get')->with('jobName')->willReturn($processor);

        $this->subject->setJobs(['anotherJobName']);

        $this->assertEquals(Processor::REQUEUE, $this->subject->process($message, $this->createMock(Context::class)));
    }

    /**
     * @test
     * @dataProvider provideResultData
     */
    public function sendsExpectedReplies($processorResult, ArrayCollection $expectedReplies)
    {
        $message = $this->createMessage('jobId', 'jobName', 'body', 'replyQueue');
        $context = $this->createMock(Context::class);
        $processor = $this->createMock(ProcessorInterface::class);

        $this->registry->expects($this->atLeastOnce())->method('get')->with('jobName')->willReturn($processor);

        $processor->expects($this->once())->method('process')->with('body')->willReturn($processorResult);

        $this->assertSendsMessage($context, 'jobId', $expectedReplies);

        $this->assertEquals(Processor::ACK, $this->subject->process($message, $context));
    }

    /**
     * @test
     */
    public function createsProcessorContext()
    {
        $message = $this->createMessage('jobId', 'jobName', 'body', 'replyQueue');
        $context = $this->createMock(Context::class);
        $processor = $this->createMock(ProcessorInterface::class);

        $this->registry->expects($this->atLeastOnce())->method('get')->with('jobName')->willReturn($processor);

        $processor->expects($this->once())->method('process')->willReturnCallback(function (
            $input,
            \Abc\Job\Processor\Context $context
        ) {
            Assert::assertEquals('jobId', $context->getJobId());

            return Result::COMPLETE;
        });

        $this->subject->process($message, $context);
    }

    /**
     * @test
     */
    public function sendsReplyOnProcessorException()
    {
        $context = $this->createMock(Context::class);
        $message = $this->createMessage('jobId', 'jobName', 'messageBody', 'replyQueue');

        $processor = $this->createMock(ProcessorInterface::class);
        $this->registry->expects($this->atLeastOnce())->method('get')->with('jobName')->willReturn($processor);

        $processor->expects($this->once())->method('process')->with('messageBody')->willThrowException(new \Exception());

        $expectedReplies = new ArrayCollection([
            ['expectedStatus' => Status::RUNNING, 'expectedOutput' => null, 'expectsProcessingTime' => false],
            ['expectedStatus' => Result::FAILED, 'expectedOutput' => null, 'expectsProcessingTime' => true],
        ]);

        $this->assertSendsMessage($context, 'jobId', $expectedReplies);

        $this->subject->process($message, $context);
    }

    public static function provideResultData()
    {
        return [
            [
                Result::COMPLETE,
                new ArrayCollection([
                    [
                        'expectedStatus' => Status::RUNNING,
                        'expectedOutput' => null,
                        'expectsProcessingTime' => false,
                    ],
                    [
                        'expectedStatus' => Result::COMPLETE,
                        'expectedOutput' => null,
                        'expectsProcessingTime' => true,
                    ],
                ]),
            ],
            [
                Result::FAILED,
                new ArrayCollection([
                    [
                        'expectedStatus' => Status::RUNNING,
                        'expectedOutput' => null,
                        'expectsProcessingTime' => false,
                    ],
                    [
                        'expectedStatus' => Result::FAILED,
                        'expectedOutput' => null,
                        'expectsProcessingTime' => true,
                    ],
                ]),
            ],
            [
                new Result(Result::COMPLETE, 'output'),
                new ArrayCollection([
                    [
                        'expectedStatus' => Status::RUNNING,
                        'expectedOutput' => null,
                        'expectsProcessingTime' => false,
                    ],
                    [
                        'expectedStatus' => Result::COMPLETE,
                        'expectedOutput' => 'output',
                        'expectsProcessingTime' => true,
                    ],
                ]),
            ],
            [
                new Result(Result::FAILED, 'output'),
                new ArrayCollection([
                    [
                        'expectedStatus' => Status::RUNNING,
                        'expectedOutput' => null,
                        'expectsProcessingTime' => false,
                    ],
                    [
                        'expectedStatus' => Result::FAILED,
                        'expectedOutput' => 'output',
                        'expectsProcessingTime' => true,
                    ],
                ]),
            ],
        ];
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $context
     * @param string $expectedCorrelationId
     * @param ArrayCollection $expectedRepliesStack
     */
    private function assertSendsMessage(
        MockObject $context,
        string $expectedCorrelationId,
        ArrayCollection $expectedRepliesStack
    ) {
        $replyMessage = $this->createMock(Message::class);

        $context->expects($this->any())->method('createMessage')->with($this->callback(function ($body) use (
            $expectedRepliesStack
        ) {
            $expectations = $expectedRepliesStack->current();
            $replyArray = json_decode($body, true);
            Assert::assertEquals($expectations['expectedStatus'], $replyArray['status']);
            Assert::assertEquals($expectations['expectedOutput'], $replyArray['output']);
            Assert::assertGreaterThanOrEqual(time(), $replyArray['createdTimestamp']);
            $expectations['expectsProcessingTime'] ? Assert::assertGreaterThan(0, $replyArray['processingTime']) : Assert::assertEquals(0, $replyArray['processingTime']);
            $expectedRepliesStack->remove(0);

            return true;
        }))->willReturn($replyMessage);

        $replyQueue = $this->createMock(Queue::class);
        $context->expects($this->any())->method('createQueue')->with('replyQueue')->willReturn($replyQueue);

        $producer = $this->createMock(Producer::class);
        $context->expects($this->any())->method('createProducer')->willReturn($producer);

        $producer->expects($this->exactly($expectedRepliesStack->count()))->method('send')->with($replyQueue, $replyMessage);

        $replyMessage->expects($this->any())->method('setCorrelationId')->with($expectedCorrelationId);
        $replyMessage->expects($this->any())->method('setTimestamp')->with($this->greaterThanOrEqual(time()));
        $replyMessage->expects($this->any())->method('setMessageId')->with($this->callback(function ($id) {
            Assert::assertTrue(Uuid::isValid($id));

            return true;
        }));
    }

    private function createMessage(string $jobId, string $jobName, string $body, string $replyQueue): MockObject
    {
        $message = $this->createMock(Message::class);
        $message->expects($this->any())->method('getCorrelationId')->willReturn($jobId);
        $message->expects($this->any())->method('getHeader')->with(Config::NAME, false)->willReturn($jobName);
        $message->expects($this->any())->method('getBody')->willReturn($body);
        $message->expects($this->any())->method('getReplyTo')->willReturn($replyQueue);

        return $message;
    }
}
