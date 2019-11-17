<?php

namespace Abc\Job\Tests\Schedule;

use Abc\Job\CronJobManager;
use Abc\Job\Model\CronJobInterface;
use Abc\Job\Schedule\ScheduleProvider;
use Abc\Scheduler\ScheduleInterface as BaseScheduleInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScheduleProviderTest extends TestCase
{
    /**
     * @var CronJobManager|MockObject
     */
    private $cronJobManager;

    /**
     * @var ScheduleProvider
     */
    private $subject;

    public function setUp(): void
    {
        $this->cronJobManager = $this->createMock(CronJobManager::class);
        $this->subject = new ScheduleProvider($this->cronJobManager);
    }

    public function testProviderSchedules()
    {
        $this->cronJobManager->expects($this->once())->method('list')->with(null, null, 10, 20)->willReturn(['foobar']);

        $this->assertEquals(['foobar'], $this->subject->provideSchedules(10, 20));
    }

    public function testSave()
    {
        /** @var CronJobInterface $cronJob */
        $cronJob = $this->createMock(CronJobInterface::class);

        $this->cronJobManager->expects($this->once())->method('update')->with($cronJob);

        $this->subject->save($cronJob);
    }

    public function testSaveWithInvalidSchedule()
    {
        /** @var BaseScheduleInterface $schedule */
        $schedule = $this->createMock(BaseScheduleInterface::class);

        $this->expectException(\InvalidArgumentException::class);

        $this->subject->save($schedule);
    }
}
