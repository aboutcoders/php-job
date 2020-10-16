<?php

namespace Abc\Job\Tests;

use Abc\Job\Job;
use Abc\Job\JobFilter;
use Abc\Job\JobManager;
use Abc\Job\Model\JobInterface;
use Abc\Job\Model\JobManagerInterface;
use Abc\Job\PreSaveExtension\PreSaveExtensionInterface;
use Abc\Job\Type;
use PHPUnit\Framework\TestCase;

class JobManagerTest extends TestCase
{
    /**
     * @var JobManagerInterface
     */
    private $jobManager;

    /**
     * @var PreSaveExtensionInterface
     */
    private $extension;

    /**
     * @var JobManager
     */
    private $subject;

    public function setUp(): void
    {
        $this->jobManager = $this->createMock(JobManagerInterface::class);
        $this->extension = $this->createMock(PreSaveExtensionInterface::class);
        $this->subject = new JobManager($this->jobManager, $this->extension);
    }

    public function testCreate()
    {
        $job = $this->createMock(JobInterface::class);

        $this->jobManager->expects($this->once())->method('create')->willReturn($job);

        $this->assertSame($job, $this->subject->create(new Job(Type::JOB(), 'name')));
    }

    public function testDelete()
    {
        $job = $this->createMock(JobInterface::class);

        $this->jobManager->expects($this->once())->method('delete')->with($job);

        $this->subject->delete($job);
    }

    public function testDeleteAll()
    {
        $this->jobManager->expects($this->once())->method('deleteAll')->with()->willReturn(1111);

        $this->assertSame(1111, $this->subject->deleteAll());
    }

    public function testRefresh()
    {
        $job = $this->createMock(JobInterface::class);

        $this->jobManager->expects($this->once())->method('refresh')->with($job);

        $this->subject->refresh($job);;
    }

    public function testSave()
    {
        $job = $this->createMock(JobInterface::class);

        $this->extension->expects($this->once())->method('onPreSave')->with($job);
        $this->jobManager->expects($this->once())->method('save')->with($job);

        $this->subject->save($job);
    }

    public function testFind()
    {
        $id = 'someId';
        $job = $this->createMock(JobInterface::class);

        $this->jobManager->expects($this->once())->method('find')->with($id)->willReturn($job);

        $this->assertSame($job, $this->subject->find($id));
    }

    public function testFindWithNull()
    {
        $id = 'someId';

        $this->jobManager->expects($this->once())->method('find')->with($id)->willReturn(null);

        $this->assertNull($this->subject->find($id));
    }

    public function testFindBy()
    {
        $job = $this->createMock(JobInterface::class);
        $filter = new JobFilter();

        $this->jobManager->expects($this->once())->method('findBy')->with($filter)->willReturn([$job]);

        $this->assertSame([$job], $this->subject->findBy($filter));;
    }

    public function testFindByWithNull()
    {
        $job = $this->createMock(JobInterface::class);

        $this->jobManager->expects($this->once())->method('findBy')->with()->willReturn([$job]);

        $this->assertSame([$job], $this->subject->findBy());;
    }

    public function testExistsConcurrent()
    {
        $job = $this->createMock(Job::class);

        $this->jobManager->expects($this->once())->method('existsConcurrent')->with($job)->willReturn(true);

        $this->assertTrue($this->subject->existsConcurrent($job));
    }
}
