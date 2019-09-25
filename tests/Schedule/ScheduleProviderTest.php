<?php

namespace Abc\Job\Tests\Schedule;

use Abc\Job\Model\ScheduleManagerInterface;
use Abc\Job\Model\ScheduleInterface;
use Abc\Job\Schedule\ScheduleProvider;
use Abc\Scheduler\ScheduleInterface as BaseScheduleInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScheduleProviderTest extends TestCase
{
    /**
     * @var ScheduleManagerInterface|MockObject
     */
    private $scheduleManager;

    /**
     * @var ScheduleProvider
     */
    private $subject;

    public function setUp(): void
    {
        $this->scheduleManager = $this->createMock(ScheduleManagerInterface::class);
        $this->subject = new ScheduleProvider($this->scheduleManager);
    }

    public function testProviderSchedules()
    {
        $this->scheduleManager->expects($this->once())->method('findBy')->with([], [], 10, 20)->willReturn(['foobar']);

        $this->assertEquals(['foobar'], $this->subject->provideSchedules(10, 20));
    }

    public function testSave()
    {
        $schedule = $this->createMock(ScheduleInterface::class);

        $this->scheduleManager->expects($this->once())->method('save')->with($schedule);

        $this->subject->save($schedule);
    }

    public function testSaveWithInvalidSchedule()
    {
        $schedule = $this->createMock(BaseScheduleInterface::class);

        $this->expectException(\InvalidArgumentException::class);

        $this->subject->save($schedule);
    }
}
