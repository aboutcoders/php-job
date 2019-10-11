<?php

namespace Abc\Job\Tests\Schedule;

use Abc\Job\Job;
use Abc\Job\JobServerInterface;
use Abc\Job\Model\ScheduleInterface;
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

    public function testProcessWithInvalidSchedule()
    {
        $schedule = $this->createMock(BaseScheduleInterface::class);

        $this->expectException(\InvalidArgumentException::class);

        $this->subject->process($schedule);
    }

    public function testProcess()
    {
        $job = Job::fromArray([
            'type' => Type::JOB(),
            'name' => 'someName',
        ]);

        $schedule = $this->createMock(ScheduleInterface::class);
        $schedule->expects($this->any())->method('getJobJson')->willReturn($job->toJson());

        $this->jobServer->expects($this->once())->method('process')->with($this->equalTo($job));

        $this->subject->process($schedule);
    }
}
