<?php

namespace Abc\Job\Tests;

use Abc\Job\Broker\ProducerInterface;
use Abc\Job\Doctrine\JobManager;
use Abc\Job\Filter;
use Abc\Job\Job;
use Abc\Job\Result;
use Abc\Job\JobServer;
use Abc\Job\Model\JobInterface;
use Abc\Job\Model\JobManagerInterface;
use Abc\Job\Status;
use Abc\Job\Type;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class JobServerTest extends TestCase
{
    /**
     * @var  ProducerInterface|MockObject
     */
    private $producer;

    /**
     * @var JobManagerInterface|MockObject
     */
    private $jobManager;

    /**
     * @var JobServer
     */
    private $subject;

    public function setUp(): void
    {
        $this->producer = $this->createMock(ProducerInterface::class);
        $this->jobManager = $this->createMock(JobManagerInterface::class);
        $this->subject = new JobServer($this->producer, $this->jobManager, new NullLogger());
    }

    public function testAllWithoutFilter()
    {
        $this->jobManager->expects($this->once())->method('findBy')->with()->willReturn([$this->createMock(JobInterface::class)]);

        $results = $this->subject->all();

        $this->assertEquals(1, count($results));
        $this->assertInstanceOf(Result::class, $results[0]);
    }

    public function testAllWithFilter()
    {
        $filter = new Filter();

        $this->jobManager->expects($this->once())->method('findBy')->with($filter)->willReturn([$this->createMock(JobInterface::class)]);

        $results = $this->subject->all($filter);

        $this->assertEquals(1, count($results));
        $this->assertInstanceOf(Result::class, $results[0]);
    }

    public function testProcessWithJob()
    {
        $job = new Job(Type::JOB(), 'jobName');

        $managedJob = JobManager::fromArray(new \Abc\Job\Model\Job(), $job->toArray());

        $this->jobManager->expects($this->at(0))->method('create')->with($job)->willReturn($managedJob);

        $this->jobManager->expects($this->at(1))->method('save')->with($managedJob);

        $this->producer->expects($this->once())->method('sendMessage')->with($managedJob);

        $this->jobManager->expects($this->at(1))->method('save')->with($managedJob);

        $this->subject->process($job);

        $this->assertEquals(Status::SCHEDULED, $managedJob->getStatus());
    }

    public function testProcessWithSequence()
    {
        $child_A = new Job(Type::JOB(), 'child_A', 'input_child_A');
        $child_B = new Job(Type::JOB(), 'child_B', 'input_child_B');
        $sequence = new Job(Type::SEQUENCE(), 'sequence', 'input_sequence', [$child_A, $child_B]);

        $managedSequence = JobManager::fromArray(new \Abc\Job\Model\Job(), $sequence->toArray());
        $managedChild_A = $this->findChild('child_A', $managedSequence);
        $managedChild_B = $this->findChild('child_B', $managedSequence);

        $this->jobManager->expects($this->at(0))->method('create')->with($sequence)->willReturn($managedSequence);

        $this->jobManager->expects($this->at(1))->method('save')->with($managedSequence);

        $this->producer->expects($this->once())->method('sendMessage')->with($managedChild_A);

        $this->jobManager->expects($this->at(1))->method('save')->with($managedSequence);

        $this->subject->process($sequence);

        $this->assertEquals(Status::SCHEDULED, $managedChild_A->getStatus());
        $this->assertEquals(Status::WAITING, $managedChild_B->getStatus());
    }

    public function testProcessWithBatch()
    {
        $child_A = new Job(Type::JOB(), 'child_A', 'input_child_A');
        $child_B = new Job(Type::JOB(), 'child_B', 'input_child_B');
        $batch = new Job(Type::BATCH(), 'sequence', 'input_batch', [$child_A, $child_B]);

        $managedBatch = JobManager::fromArray(new \Abc\Job\Model\Job(), $batch->toArray());
        $managedChild_A = $this->findChild('child_A', $managedBatch);
        $managedChild_B = $this->findChild('child_B', $managedBatch);

        $this->jobManager->expects($this->at(0))->method('create')->with($batch)->willReturn($managedBatch);

        $this->jobManager->expects($this->at(1))->method('save')->with($managedBatch);

        $this->producer->expects($this->at(0))->method('sendMessage')->with($managedChild_A);
        $this->producer->expects($this->at(1))->method('sendMessage')->with($managedChild_B);

        $this->jobManager->expects($this->at(1))->method('save')->with($managedBatch);

        $this->subject->process($batch);

        $this->assertEquals(Status::SCHEDULED, $managedChild_A->getStatus());
        $this->assertEquals(Status::SCHEDULED, $managedChild_B->getStatus());
    }

    public function testRestartWithNonExistingJob()
    {
        $this->jobManager->expects($this->once())->method('find')->willReturn(null);

        $this->assertNull($this->subject->restart('someId'));
    }

    public function testRestartWithJob()
    {
        $managedJob = self::createManagedJob(Type::JOB());
        $resetJob = self::createResetJobFromJob($managedJob);

        $this->jobManager->expects($this->once())->method('find')->willReturn($managedJob);

        $this->jobManager->expects($this->once())->method('save')->with($this->equalTo($resetJob));

        $this->producer->expects($this->once())->method('sendMessage')->with($this->equalTo($resetJob));

        $this->assertInstanceOf(Result::class, $this->subject->restart('someId'));
    }

    public function testRestartWithSequence()
    {
        $managedChild = self::createManagedJob(Type::JOB());
        $managedSequence = self::createManagedJob(Type::SEQUENCE());
        $managedSequence->addChild($managedChild);

        $resetChild = static::createResetJobFromJob($managedChild);
        $resetSequence = self::createResetJobFromJob($managedSequence);
        $resetSequence->addChild($resetChild);

        $this->jobManager->expects($this->once())->method('find')->willReturn($managedSequence);
        $this->jobManager->expects($this->once())->method('save')->with($this->equalTo($resetSequence));

        $this->producer->expects($this->once())->method('sendMessage')->with($this->equalTo($resetChild));

        $this->assertInstanceOf(Result::class, $this->subject->restart('someId'));
    }

    public function testRestartWithBatch()
    {
        $managedChild_A = self::createManagedJob(Type::JOB());
        $managedChild_B = self::createManagedJob(Type::JOB());
        $managedBatch = self::createManagedJob(Type::BATCH());
        $managedBatch->addChild($managedChild_A);
        $managedBatch->addChild($managedChild_B);

        $resetChild_A = static::createResetJobFromJob($managedChild_A);
        $resetChild_B = static::createResetJobFromJob($managedChild_B);
        $resetBatch = self::createResetJobFromJob($managedBatch);
        $resetBatch->addChild($resetChild_A);
        $resetBatch->addChild($resetChild_B);

        $this->jobManager->expects($this->once())->method('find')->willReturn($managedBatch);
        $this->jobManager->expects($this->once())->method('save')->with($this->equalTo($resetBatch));

        // fixme:: does not work because of differences in the children
        //$this->producer->expects($this->any())->method('sendMessage')->withConsecutive([$this->equalTo($resetChild_A)], [$this->equalTo($resetChild_B)]);

        $this->assertInstanceOf(Result::class, $this->subject->restart('someId'));
    }

    public function testCancelWithNonExistingJob()
    {
        $this->jobManager->expects($this->once())->method('find')->willReturn(null);

        $this->assertNull($this->subject->cancel('someId'));
    }

    public function testCancelWithJobWaiting()
    {
        $job = new \Abc\Job\Model\Job();
        $job->setType(Type::JOB());
        $job->setStatus(Status::WAITING);

        $expectedJob = new \Abc\Job\Model\Job();
        $expectedJob->setType(Type::JOB());
        $expectedJob->setStatus(Status::CANCELLED);

        $this->jobManager->expects($this->once())->method('find')->with('someId')->willReturn($job);

        $this->jobManager->expects($this->once())->method('save')->with($this->equalTo($expectedJob));

        $this->assertTrue($this->subject->cancel('someId'));
    }

    public function testCancelWithJobNotWaiting()
    {
        $job = new \Abc\Job\Model\Job();
        $job->setType(Type::JOB());
        $job->setStatus(Status::SCHEDULED);

        $this->jobManager->expects($this->once())->method('find')->with('someId')->willReturn($job);

        $this->assertFalse($this->subject->cancel('someId'));
    }

    public function testCancelWithBatchNotWaiting()
    {
        $batch = new \Abc\Job\Model\Job();
        $batch->setType(Type::BATCH());
        $batch->setStatus(Status::SCHEDULED);

        $this->jobManager->expects($this->once())->method('find')->with('someId')->willReturn($batch);

        $this->assertFalse($this->subject->cancel('someId'));
    }

    public function testCancelWithBatchWaiting()
    {
        $child = new \Abc\Job\Model\Job();
        $child->setType(Type::JOB());
        $child->setStatus(Status::WAITING);

        $batch = new \Abc\Job\Model\Job();
        $batch->setType(Type::BATCH());
        $batch->setStatus(Status::WAITING);
        $batch->addChild($child);

        $expectedChild = new \Abc\Job\Model\Job();
        $expectedChild->setType(Type::JOB());
        $expectedChild->setStatus(Status::CANCELLED);

        $expectedBatch = new \Abc\Job\Model\Job();
        $expectedBatch->setType(Type::BATCH());
        $expectedBatch->setStatus(Status::CANCELLED);
        $expectedBatch->addChild($expectedChild);

        $this->jobManager->expects($this->once())->method('find')->with('someId')->willReturn($batch);

        $this->jobManager->expects($this->once())->method('save')->with($this->equalTo($expectedBatch));

        $this->assertTrue($this->subject->cancel('someId'));
    }

    public function testCancelWithSequence()
    {
        $child_A = new \Abc\Job\Model\Job();
        $child_A->setType(Type::JOB());
        $child_A->setStatus(Status::RUNNING);

        $child_B = new \Abc\Job\Model\Job();
        $child_B->setType(Type::JOB());

        $child_C = new \Abc\Job\Model\Job();
        $child_C->setType(Type::JOB());

        $sequence = new \Abc\Job\Model\Job();
        $sequence->setType(Type::SEQUENCE());
        $sequence->setStatus(Status::SCHEDULED);
        $sequence->addChild($child_A);
        $sequence->addChild($child_B);
        $sequence->addChild($child_C);

        $expectedChild_B = new \Abc\Job\Model\Job();
        $expectedChild_B->setType(Type::JOB());
        $expectedChild_B->setStatus(Status::CANCELLED);

        $expectedChild_C = new \Abc\Job\Model\Job();
        $expectedChild_C->setType(Type::JOB());
        $expectedChild_C->setStatus(Status::CANCELLED);

        $expectedSequence = new \Abc\Job\Model\Job();
        $expectedSequence->setType(Type::SEQUENCE());
        $expectedSequence->setStatus(Status::SCHEDULED);
        $expectedSequence->addChild($child_A);
        $expectedSequence->addChild($expectedChild_B);
        $expectedSequence->addChild($expectedChild_C);

        $this->jobManager->expects($this->once())->method('find')->with('someId')->willReturn($sequence);

        $this->jobManager->expects($this->once())->method('save')->with($this->equalTo($expectedSequence));

        $this->assertTrue($this->subject->cancel('someId'));
    }

    public function testResultWithNonExistingJob()
    {
        $this->jobManager->expects($this->once())->method('find')->willReturn(null);

        $this->assertNull($this->subject->result('someId'));
    }

    public function testResult()
    {
        $job = $this->createMock(JobInterface::class);

        $this->jobManager->expects($this->once())->method('find')->willReturn($job);

        $this->assertInstanceOf(Result::class, $this->subject->result('someId'));
    }

    public function testDeleteWithNonExistingJob()
    {
        $this->jobManager->expects($this->once())->method('find')->willReturn(null);

        $this->assertNull($this->subject->delete('someId'));
    }

    public function testDelete()
    {
        $job = $this->createMock(JobInterface::class);

        $this->jobManager->expects($this->once())->method('find')->willReturn($job);
        $this->jobManager->expects($this->once())->method('delete')->with($job);

        $this->assertTrue($this->subject->delete('someId'));
    }

    public function testTrigger()
    {
        $job = new \Abc\Job\Model\Job();
        $job->setType(Type::JOB());

        $this->producer->expects($this->once())->method('sendMessage')->with($job);

        $this->jobManager->expects($this->once())->method('save')->with($job);

        $this->subject->trigger($job);
    }

    private function findChild(string $name, JobInterface $managedSequence): ?JobInterface
    {
        foreach ($managedSequence->getChildren() as $child) {
            if ($name == $child->getName()) {
                return $child;
            }

            if (in_array($child->getType(), [Type::SEQUENCE(), Type::BATCH()])) {
                return $this->findChild($name, $child);
            }
        }

        return null;
    }

    private static function createManagedJob(Type $type): JobInterface
    {
        $managedJob = new \Abc\Job\Model\Job();
        $managedJob->setType($type);
        $managedJob->setStatus(Status::FAILED);
        $managedJob->setProcessingTime(0.123);
        $managedJob->setOutput('someOutPut');
        $managedJob->setCompletedAt(new \DateTime());
        $managedJob->setExternalId('externalId');
        $managedJob->setInput('input');
        $managedJob->setName('jobName');

        return $managedJob;
    }

    private function createResetJobFromJob(JobInterface $job): JobInterface
    {
        $resetJob = new \Abc\Job\Model\Job();
        $resetJob->setType($job->getType());
        $resetJob->setStatus(Status::SCHEDULED);
        $resetJob->setExternalId($job->getExternalId());
        $resetJob->setInput($job->getInput());
        $resetJob->setName($job->getName());

        return $resetJob;
    }
}
