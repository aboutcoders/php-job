<?php

namespace Abc\Job\Tests;

use Abc\Job\CronJobFilter;
use Abc\Job\CronJobManager;
use Abc\Job\Job;
use Abc\Job\Model\CronJobInterface;
use Abc\Job\Model\CronJobManagerInterface;
use Abc\Job\Type;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CronJobManagerTest extends TestCase
{
    /**
     * @var CronJobManagerInterface|MockObject
     */
    private $entityManager;

    /**
     * @var CronJobManager
     */
    private $subject;

    public function setUp(): void
    {
        $this->entityManager = $this->createMock(CronJobManagerInterface::class);
        $this->subject = new CronJobManager($this->entityManager);
    }

    public function testList()
    {
        $filter = new CronJobFilter();
        $cronJob = $this->createMock(CronJobInterface::class);

        $this->entityManager->expects($this->once())->method('findBy')->with($filter)->willReturn([$cronJob]);

        $this->assertSame([$cronJob], $this->subject->list($filter));
    }

    public function testFind()
    {
        $cronJob = $this->createMock(CronJobInterface::class);

        $this->entityManager->expects($this->once())->method('find')->with('someId')->willReturn($cronJob);

        $this->assertSame($cronJob, $this->subject->find('someId'));
    }

    public function testCreate()
    {
        $job = new Job(Type::JOB(), 'someJob');

        /** @var CronJobInterface $managedCronJob */
        $managedCronJob = $this->createMock(CronJobInterface::class);

        $this->entityManager->expects($this->once())->method('create')->with('someSchedule', $job)->willReturn($managedCronJob);
        $this->entityManager->expects($this->once())->method('save')->with($managedCronJob);

        $this->assertSame($managedCronJob, $this->subject->create('someSchedule', $job));
    }

    public function testUpdate()
    {
        /** @var CronJobInterface $cronJob */
        $cronJob = $this->createMock(CronJobInterface::class);

        $this->entityManager->expects($this->once())->method('getClass')->willReturn(CronJobInterface::class);
        $this->entityManager->expects($this->once())->method('save')->with($cronJob);

        $this->subject->update($cronJob);
    }

    public function testUpdateWithInvalidArgument()
    {
        /** @var CronJobInterface $cronJob */
        $cronJob = $this->createMock(CronJobInterface::class);

        $this->entityManager->expects($this->once())->method('getClass')->willReturn(\stdClass::class);
        $this->entityManager->expects($this->never())->method('save');

        $this->expectException(\InvalidArgumentException::class);

        $this->subject->update($cronJob);
    }

    public function testDelete()
    {
        /** @var CronJobInterface $cronJob */
        $cronJob = $this->createMock(CronJobInterface::class);

        $this->entityManager->expects($this->once())->method('getClass')->willReturn(CronJobInterface::class);
        $this->entityManager->expects($this->once())->method('delete')->with($cronJob);

        $this->subject->delete($cronJob);
    }

    public function testDeleteWithInvalidArgument()
    {
        /** @var CronJobInterface $cronJob */
        $cronJob = $this->createMock(CronJobInterface::class);

        $this->entityManager->expects($this->once())->method('getClass')->willReturn(\stdClass::class);
        $this->entityManager->expects($this->never())->method('delete');

        $this->expectException(\InvalidArgumentException::class);

        $this->subject->delete($cronJob);
    }

    public function testDeleteAll()
    {
        $this->entityManager->expects($this->once())->method('deleteAll')->willReturn(21);

        $this->assertEquals(21, $this->subject->deleteAll());
    }
}
