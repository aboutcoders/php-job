<?php

namespace Abc\Job\Tests;

use Abc\Job\Job;
use Abc\Job\Type;
use PHPUnit\Framework\TestCase;

class JobTest extends TestCase
{
    /**
     * @param array $arguments
     * @param $expectedType
     * @dataProvider provideConstructorArguments
     */
    public function testConstructor(
        array $arguments,
        $expectedType,
        $expectedName,
        $expectedInput,
        $expectedChildren,
        $expectedIsAllowFailure
    ) {
        $job = new Job(...$arguments);

        $this->assertEquals($expectedType, $job->getType());
        $this->assertSame($expectedName, $job->getName());
        $this->assertSame($expectedInput, $job->getInput());
        $this->assertSame($expectedIsAllowFailure, $job->isAllowFailure());
        $this->assertEquals($expectedChildren, $job->getChildren());
    }

    /**
     * @dataProvider provideInvalidCollection
     */
    public function testThrowsExceptionOnInvalidCollection($type, $children)
    {
        $this->expectException(\InvalidArgumentException::class);

        Job::fromArray([
            'type' => $type,
            'children' => $children,
        ]);
    }

    public function testThrowsExceptionIfTypeJobAndNameNull()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Job(Type::JOB());
    }

    public function testThrowsExceptionIfTypeJobWithChildren()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Job(Type::JOB(), 'name', null, [new Job(Type::JOB(), 'name'), new Job(Type::JOB(), 'name')]);
    }

    public static function provideConstructorArguments(): array
    {
        return [
            [[Type::JOB(), 'name'], Type::JOB(), 'name', null, [], false],
            [[Type::JOB(), 'name', 'input', [], true], Type::JOB(), 'name', 'input', [], true],
            [
                [Type::SEQUENCE(), null, null, [new Job(Type::JOB(), 'name1'), new Job(Type::JOB(), 'name1')]],
                Type::SEQUENCE(),
                null,
                null,
                [new Job(Type::JOB(), 'name1'), new Job(Type::JOB(), 'name1')],
                false,
            ],
        ];
    }

    public static function provideInvalidCollection(): array
    {
        return [
            [Type::SEQUENCE(), []],
            [Type::BATCH(), []],
        ];
    }

    public function testConversion()
    {
        $job = Job::fromArray([
            'type' => Type::SEQUENCE(),
            'name' => 'mySequence',
            'input' => 'someInput',
            'children' => [
                [
                    'type' => Type::JOB(),
                    'name' => 'myJob1',
                    'input' => 'someInput',
                ],
                [
                    'type' => Type::JOB(),
                    'name' => 'myJob2',
                    'input' => 'someInput',
                ],
            ],
        ]);

        $json = $job->toJson();

        $assocArray = json_decode($json, true);

        $this->assertEquals($job->getType(), $assocArray['type']);
        $this->assertEquals($job->getName(), $assocArray['name']);
        $this->assertEquals($job->getInput(), $assocArray['input']);
        $this->assertEquals($job->isAllowFailure(), $assocArray['allowFailure']);
        $this->assertEquals($job->getChildren()[0]->getType(), $assocArray['children'][0]['type']);
        $this->assertEquals($job->getChildren()[0]->getName(), $assocArray['children'][0]['name']);
        $this->assertEquals($job->getChildren()[0]->getInput(), $assocArray['children'][0]['input']);

        $decodedJob = Job::fromJson($json);
        $this->assertEquals($job, $decodedJob);
    }
}
