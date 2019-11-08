<?php

namespace Abc\Job\Tests\Doctrine;

use Abc\Job\Doctrine\CronJobManager;
use Abc\Job\Job;
use Abc\Job\Model\CronJob;
use Abc\Job\Model\CronJobInterface;
use Abc\Job\Type;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CronJobManagerTest extends TestCase
{
    /**
     * @var ObjectManager|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var ObjectRepository|MockObject
     */
    protected $repositoryMock;

    /**
     * @var CronJobManager
     */
    private $subject;

    public function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(ObjectManager::class);
        $this->repositoryMock = $this->createMock(ObjectRepository::class);
        $this->subject = new CronJobManager($this->objectManagerMock, CronJob::class);
    }

    /**
     * @param $andFlush
     * @dataProvider provideFlushValues
     */
    public function testSave($andFlush)
    {
        $cronJob = $this->subject->create('* * * * *', new Job(Type::JOB(), 'someJob'));

        $callback = function (CronJobInterface $param) use ($cronJob) {
            Assert::assertSame($cronJob, $param);
            Assert::assertInstanceOf(\DateTime::class, $param->getCreatedAt());
            Assert::assertInstanceOf(\DateTime::class, $param->getUpdatedAt());
            Assert::assertEquals($cronJob->getJob()->toJson(), $cronJob->getJobJson());
            Assert::assertEquals($cronJob->getJob()->getName(), $cronJob->getName());

            return true;
        };

        $this->objectManagerMock->expects($this->once())->method('persist')->with($this->callback($callback));

        if ($andFlush) {
            $this->objectManagerMock->expects($this->once())->method('flush');
        }

        $this->subject->save($cronJob, $andFlush);
    }

    /**
     * @param $andFlush
     * @dataProvider provideFlushValues
     */
    public function testDelete($andFlush)
    {
        $scheduledJob = $this->subject->create('* * * * *', new Job(Type::JOB(), 'someJob'));

        $this->objectManagerMock->expects($this->once())->method('remove')->with($scheduledJob);

        if ($andFlush) {
            $this->objectManagerMock->expects($this->once())->method('flush');
        }

        $this->subject->delete($scheduledJob, $andFlush);
    }

    public function provideFlushValues(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
