<?php

namespace Abc\Job\Tests\Model;

use Abc\Job\Job;
use Abc\Job\Model\CronJob;
use Abc\Job\Model\CronJobInterface;
use Abc\Job\Type;
use PHPUnit\Framework\TestCase;

class CronJobTest extends TestCase
{
    public function testGetScheduleWithConstructorNotCalled()
    {
        /** @var CronJob $subject */
        $subject = $this->getMockBuilder(CronJob::class)->disableOriginalConstructor()->setMethods()->getMock();

        $this->expectException(\LogicException::class);

        $subject->getSchedule();
    }

    public function testGetJobWithJobJsonSet()
    {
        /** @var CronJob $subject */
        $subject = $this->getMockBuilder(CronJob::class)->disableOriginalConstructor()->setMethods()->getMock();

        $job = Job::fromArray([
            'type' => Type::JOB(),
            'name' => 'someName',
        ]);

        $jobJson = $job->toJson();

        $subject->setJobJson($jobJson);

        $this->assertEquals($job, $subject->getJob());
    }

    public function testGetJobWithJobJsonNull()
    {
        /** @var CronJob $subject */
        $subject = $this->getMockBuilder(CronJob::class)->disableOriginalConstructor()->setMethods()->getMock();

        $this->expectException(\LogicException::class);

        $subject->getJob();
    }

    public function testGetJobWithJobSet()
    {
        $subject = new CronJob('* * * * *', Job::fromArray([
            'type' => Type::JOB(),
            'name' => 'someName',
        ]));

        $job = Job::fromArray([
            'type' => Type::JOB(),
            'name' => 'someName',
        ]);

        $subject->setJob($job);

        $this->assertSame($job, $subject->getJob());
    }

    public function testGetJobJsonWithoutConstructorCalled() {
        /** @var CronJob $subject */
        $subject = $this->getMockBuilder(CronJob::class)->disableOriginalConstructor()->setMethods()->getMock();

        $this->assertNull($subject->getJobJson());
    }

    public function testGetJobJsonWithoutConstructorCalledAndJobJsonSet() {
        /** @var CronJob $subject */
        $subject = $this->getMockBuilder(CronJob::class)->disableOriginalConstructor()->setMethods()->getMock();

        $subject->setJobJson('someJson');

        $this->assertEquals('someJson', $subject->getJobJson());
    }

    public function testFromJson()
    {
        $cronJob = CronJob::fromJson(json_encode([
            'schedule' => '* * * * *',
            'type' => Type::JOB(),
            'name' => 'someName',
        ]));

        $this->assertInstanceOf(CronJobInterface::class, $cronJob);
        $this->assertSame('* * * * *', $cronJob->getSchedule());
        $this->assertEquals(Job::fromArray([
            'type' => Type::JOB(),
            'name' => 'someName',
        ]), $cronJob->getJob());
    }

    /**
     * @param string $json
     * @dataProvider provideInvalidJson
     */
    public function testFromJsonWithInvalidData(string $json)
    {
        $this->expectException(\InvalidArgumentException::class);

        CronJob::fromJson($json);
    }

    public function testToJson()
    {
        $cronJob = CronJob::fromJson(json_encode([
            'schedule' => '* * * * *',
            'type' => Type::JOB(),
            'name' => 'someName',
        ]));

        $object = json_decode($cronJob->toJson());

        $this->assertEquals($cronJob->getSchedule(), $object->schedule);
        $this->assertEquals($cronJob->getJob()->getType(), $object->type);
        $this->assertEquals($cronJob->getJob()->getName(), $object->name);
    }

    public function testFromArray()
    {
        $cronJob = CronJob::fromArray([
            'schedule' => '* * * * *',
            'type' => Type::JOB(),
            'name' => 'someName',
        ]);

        $this->assertInstanceOf(CronJobInterface::class, $cronJob);
        $this->assertSame('* * * * *', $cronJob->getSchedule());
        $this->assertEquals(Job::fromArray([
            'type' => Type::JOB(),
            'name' => 'someName',
        ]), $cronJob->getJob());
    }

    public function testToArray()
    {
        $cronJob = CronJob::fromJson(json_encode([
            'schedule' => '* * * * *',
            'type' => Type::JOB(),
            'name' => 'someName',
        ]));

        $data = $cronJob->toArray();

        $this->assertEquals($cronJob->getSchedule(), $data['schedule']);
        $this->assertEquals($cronJob->getJob()->getType(), $data['type']);
        $this->assertEquals($cronJob->getJob()->getName(), $data['name']);
        $this->assertFalse(isset($data['id']));
        $this->assertFalse(isset($data['createdAt']));
        $this->assertFalse(isset($data['updatedAt']));
    }

    public function testToArrayWithSaved()
    {
        $cronJob = CronJob::fromJson(json_encode([
            'schedule' => '* * * * *',
            'type' => Type::JOB(),
            'name' => 'someName',
        ]));

        $cronJob->setId('someId');
        $cronJob->setCreatedAt(new \DateTime("@10"));
        $cronJob->setUpdatedAt(new \DateTime("@100"));

        $data = $cronJob->toArray();

        $this->assertEquals('someId', $data['id']);
        $this->assertEquals($cronJob->getCreatedAt()->format('c'), $data['created']);
        $this->assertEquals($cronJob->getUpdatedAt()->format('c'), $data['updated']);
    }

    public function testToArraySortsKeys()
    {
        $cronJob = CronJob::fromJson(json_encode([
            'schedule' => '* * * * *',
            'type' => Type::JOB(),
            'name' => 'someName',
        ]));

        $cronJob->setId('someId');
        $cronJob->setCreatedAt(new \DateTime("@10"));
        $cronJob->setUpdatedAt(new \DateTime("@100"));

        $data = $cronJob->toArray();

        $this->assertSame([
            'id',
            'schedule',
            'type',
            'name',
            'input',
            'allowFailure',
            'externalId',
            'updated',
            'created',
            'children',
        ], array_keys($data));
    }

    /**
     * @dataProvider provideInvalidArray
     */
    public function testFromArrayWithInvalidData(array $data)
    {
        $this->expectException(\InvalidArgumentException::class);

        CronJob::fromArray($data);
    }

    public function provideInvalidArray(): array
    {
        return [
            [['someString']],
            [['schedule' => '* * * * *']],
        ];
    }

    public function provideInvalidJson()
    {
        return [
            ['someString'],
            [json_encode(['someString'])],
            [json_encode(['schedule' => '* * * * *'])],
        ];
    }
}
