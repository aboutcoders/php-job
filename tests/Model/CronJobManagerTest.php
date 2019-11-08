<?php

namespace Abc\Job\Tests\Model;

use Abc\Job\Job;

use Abc\Job\Model\CronJob;
use Abc\Job\Model\CronJobInterface;
use Abc\Job\Model\CronJobManager;
use Abc\Job\Type;
use PHPUnit\Framework\TestCase;

class CronJobManagerTest extends TestCase
{

    public function testCreate()
    {
        /** @var CronJobManager $subject */
        $subject = $this->getMockForAbstractClass(CronJobManager::class, [CronJob::class]);

        $job = new Job(Type::JOB(), 'someJob');

        $scheduledJob = $subject->create('* * * * *', $job);

        $this->assertInstanceOf(CronJobInterface::class, $scheduledJob);
        $this->assertSame($job, $scheduledJob->getJob());
        $this->assertEquals('* * * * *', $scheduledJob->getSchedule());
    }
}
