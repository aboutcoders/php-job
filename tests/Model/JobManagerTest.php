<?php

namespace Abc\Job\Tests\Model;

use Abc\Job\Job;
use Abc\Job\Model\JobInterface;
use Abc\Job\Model\JobManager;
use Abc\Job\Type;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JobManagerTest extends TestCase
{
    public function testCreate()
    {
        /** @var JobManager|MockObject $subject */
        $subject = $this->getMockBuilder(JobManager::class)->setMethods(
            [
                'getClass',
                'delete',
                'deleteAll',
                'refresh',
                'save',
                'find',
                'findBy',
                'existsConcurrent'
            ]
        )->getMock();

        $subject->expects($this->any())->method('getClass')->willReturn(\Abc\Job\Model\Job::class);

        $managedJob = $subject->create(new Job(Type::JOB(), 'someJob'));
        $this->assertInstanceOf(JobInterface::class, $managedJob);
        $this->assertInstanceOf(\Abc\Job\Model\Job::class, $managedJob);

        $this->assertEquals(Type::JOB(), $managedJob->getType());
        $this->assertEquals('someJob', $managedJob->getName());
    }

    public function testCreateWithInvalidClass()
    {
        /** @var JobManager|MockObject $subject */
        $subject = $this->getMockBuilder(JobManager::class)->setMethods(
            [
                'getClass',
                'delete',
                'deleteAll',
                'refresh',
                'save',
                'find',
                'findBy',
                'existsConcurrent'
            ]
        )->getMock();

        $subject->expects($this->any())->method('getClass')->willReturn(\stdClass::class);

        $this->expectException(\LogicException::class);

        $subject->create(new Job(Type::JOB(), 'someJob'));
    }
}
