<?php

namespace Abc\Job\Tests\Schedule;

use Abc\Job\Job;
use Abc\Job\JobServerInterface;
use Abc\Job\Model\CronJob;
use Abc\Job\Schedule\ScheduleProcessor;
use Abc\Job\Type;
use Abc\Scheduler\ScheduleInterface as BaseScheduleInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScheduleProcessorTest extends TestCase
{
    /**
     * @var JobServerInterface|MockObject
     */
    private $jobServer;

    /**
     * @var ScheduleProcessor
     */
    private $subject;

    public function setUp(): void
    {
        $this->jobServer = $this->createMock(JobServerInterface::class);
        $this->subject = new ScheduleProcessor($this->jobServer);
    }

    public function testProcessWithInvalidArgument()
    {
        /** @var BaseScheduleInterface $cronJob */
        $cronJob = $this->createMock(BaseScheduleInterface::class);

        $this->expectException(\InvalidArgumentException::class);

        $this->subject->process($cronJob);
    }

    public function testProcess()
    {
        $job = Job::fromArray([
            'type' => Type::JOB(),
            'name' => 'someName',
            'externalId' => 'someExternalId'
        ]);

        $cronJob = new CronJob('* * * * *', Job::fromArray($job->toArray()));
        $cronJob->setId('someCronJobId');

        $this->jobServer->expects($this->once())->method('process')->with($this->equalTo($job));

        $this->subject->process($cronJob);

        $this->assertNotSame($job, $cronJob->getJob());
    }
}
