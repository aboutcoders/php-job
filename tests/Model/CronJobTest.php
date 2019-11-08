<?php

namespace Abc\Job\Tests\Model;

use Abc\Job\Job;
use Abc\Job\Model\CronJob;
use Abc\Job\Model\CronJobInterface;
use Abc\Job\Type;
use PHPUnit\Framework\TestCase;

class CronJobTest extends TestCase
{
    public function testFromJson()
    {
        $scheduledJob = CronJob::fromJson(json_encode([
            'schedule' => '* * * * *',
            'type' => Type::JOB(),
            'name' => 'someName',
        ]));

        $this->assertInstanceOf(CronJobInterface::class, $scheduledJob);
        $this->assertSame('* * * * *', $scheduledJob->getSchedule());
        $this->assertEquals(Job::fromArray([
            'type' => Type::JOB(),
            'name' => 'someName',
        ]), $scheduledJob->getJob());
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
        $scheduledJob = CronJob::fromJson(json_encode([
            'schedule' => '* * * * *',
            'type' => Type::JOB(),
            'name' => 'someName',
        ]));

        $object = json_decode($scheduledJob->toJson());

        $this->assertEquals($scheduledJob->getSchedule(), $object->schedule);
        $this->assertEquals($scheduledJob->getJob()->getType(), $object->type);
        $this->assertEquals($scheduledJob->getJob()->getName(), $object->name);
    }

    public function testFromArray()
    {
        $scheduledJob = CronJob::fromArray([
            'schedule' => '* * * * *',
            'type' => Type::JOB(),
            'name' => 'someName',
        ]);

        $this->assertInstanceOf(CronJobInterface::class, $scheduledJob);
        $this->assertSame('* * * * *', $scheduledJob->getSchedule());
        $this->assertEquals(Job::fromArray([
            'type' => Type::JOB(),
            'name' => 'someName',
        ]), $scheduledJob->getJob());
    }

    public function testToArray()
    {
        $scheduledJob = CronJob::fromJson(json_encode([
            'schedule' => '* * * * *',
            'type' => Type::JOB(),
            'name' => 'someName',
        ]));

        $data = $scheduledJob->toArray();

        $this->assertEquals($scheduledJob->getSchedule(), $data['schedule']);
        $this->assertEquals($scheduledJob->getJob()->getType(), $data['type']);
        $this->assertEquals($scheduledJob->getJob()->getName(), $data['name']);
        $this->assertFalse(isset($data['id']));
        $this->assertFalse(isset($data['createdAt']));
        $this->assertFalse(isset($data['updatedAt']));
    }

    public function testToArrayWithSaved()
    {
        $scheduledJob = CronJob::fromJson(json_encode([
            'schedule' => '* * * * *',
            'type' => Type::JOB(),
            'name' => 'someName',
        ]));

        $scheduledJob->setId('someId');
        $scheduledJob->setCreatedAt(new \DateTime("@10"));
        $scheduledJob->setUpdatedAt(new \DateTime("@100"));

        $data = $scheduledJob->toArray();

        $this->assertEquals('someId', $data['id']);
        $this->assertEquals($scheduledJob->getCreatedAt()->format('c'), $data['created']);
        $this->assertEquals($scheduledJob->getUpdatedAt()->format('c'), $data['updated']);
    }

    public function testToArraySortsKeys()
    {
        $scheduledJob = CronJob::fromJson(json_encode([
            'schedule' => '* * * * *',
            'type' => Type::JOB(),
            'name' => 'someName',
        ]));

        $scheduledJob->setId('someId');
        $scheduledJob->setCreatedAt(new \DateTime("@10"));
        $scheduledJob->setUpdatedAt(new \DateTime("@100"));

        $data = $scheduledJob->toArray();

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
