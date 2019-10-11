<?php

namespace Abc\Job\Tests\Validator;

use Abc\Job\Broker\Route;
use Abc\Job\Filter;
use Abc\Job\Job;
use Abc\Job\Type;
use Abc\Job\Validator;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new Validator();
    }

    public function testValidWithInvalidClass()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->subject->validate('json', \stdClass::class);
    }

    /**
     * @dataProvider provideValidJob
     */
    public function testValidJob($job)
    {
        $json = json_encode($job);
        $errors = $this->subject->validate($json, Job::class);
        $this->assertEmpty($errors);
    }

    /**
     * @dataProvider provideInvalidJob
     */
    public function testInvalidJob($job)
    {
        $json = json_encode($job);
        $errors = $this->subject->validate($json, Job::class);
        $this->assertNotEmpty($errors);
    }

    /**
     * @param string $queryString
     * @dataProvider provideValidFilter
     */
    public function testValidFilter(string $queryString)
    {
        parse_str($queryString, $data);
        $json = json_encode((object) $data);

        $errors = $this->subject->validate($json, Filter::class);
        $this->assertEmpty($errors);
    }

    /**
     * @param string $queryString
     * @dataProvider provideInvalidFilter
     */
    public function testInvalidFilter(string $queryString)
    {
        parse_str($queryString, $data);
        $json = json_encode((object) $data);

        $errors = $this->subject->validate($json, Filter::class);
        $this->assertNotEmpty($errors);
    }

    /**
     * @dataProvider provideValidRoute
     */
    public function testValidRoute($route)
    {
        $json = json_encode($route);
        $errors = $this->subject->validate($json, Route::class);
        $this->assertEmpty($errors);
    }

    /**
     * @dataProvider provideInvalidRoute
     */
    public function testInvalidRoute($route)
    {
        $json = json_encode($route);
        $errors = $this->subject->validate($json, Route::class);
        $this->assertNotEmpty($errors);
    }

    public static function provideValidJob(): array
    {
        return [
            #0
            [
                (object) [
                    'type' => (string) Type::JOB(),
                    'name' => 'valid',
                ],
            ],
            #1
            [
                (object) [
                    'type' => (string) Type::SEQUENCE(),
                    'input' => 'valid',
                    'allowFailure' => false,
                    'externalId' => Uuid::uuid4(),
                    'children' => [
                        (object) [
                            'type' => (string) Type::JOB(),
                            'name' => 'valid',
                            'input' => 'valid',
                            'allowFailure' => false,
                            'externalId' => Uuid::uuid4(),
                        ],
                        (object) [
                            'type' => (string) Type::JOB(),
                            'name' => 'valid',
                            'input' => 'valid',
                            'allowFailure' => false,
                            'externalId' => Uuid::uuid4(),
                        ],
                    ],
                ],
            ],
            #2
            [
                (object) [
                    'type' => (string) Type::BATCH(),
                    'input' => 'valid',
                    'allowFailure' => false,
                    'externalId' => Uuid::uuid4(),
                    'children' => [
                        (object) [
                            'type' => (string) Type::JOB(),
                            'name' => 'valid',
                            'input' => 'valid',
                            'allowFailure' => false,
                            'externalId' => Uuid::uuid4(),
                        ],
                        (object) [
                            'type' => (string) Type::JOB(),
                            'name' => 'valid',
                            'input' => 'valid',
                            'allowFailure' => false,
                            'externalId' => Uuid::uuid4(),
                        ],
                    ],
                ],
            ],
            #3
            [
                (object) [
                    'type' => (string) Type::BATCH(),
                    'input' => 'valid',
                    'allowFailure' => false,
                    'externalId' => Uuid::uuid4(),
                    'children' => [
                        (object) [
                            'type' => (string) Type::SEQUENCE(),
                            'children' => [
                                (object) [
                                    'type' => (string) Type::JOB(),
                                    'name' => 'valid',
                                ],
                                (object) [
                                    'type' => (string) Type::JOB(),
                                    'name' => 'valid',
                                ],
                            ],
                        ],
                        (object) [
                            'type' => (string) Type::BATCH(),
                            'children' => [
                                (object) [
                                    'type' => (string) Type::JOB(),
                                    'name' => 'valid',
                                ],
                                (object) [
                                    'type' => (string) Type::JOB(),
                                    'name' => 'valid',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function provideInvalidJob(): array
    {
        return [
            #0
            [
                (object) [
                    'type' => (string) Type::JOB(),
                ],
            ],
            #1
            [
                (object) [
                    'name' => 'valid',
                ],
            ],
            #2
            [
                (object) [
                    'type' => (string) Type::JOB(),
                    'name' => str_repeat('a', 2),
                ],
            ],
            #3
            [
                (object) [
                    'type' => (string) Type::JOB(),
                    'name' => str_repeat('a', 21),
                ],
            ],
            #4
            [
                (object) [
                    'type' => (string) Type::SEQUENCE(),
                    'children' => [
                        (object) [
                            'type' => (string) Type::JOB(),
                            'name' => 'valid',
                        ],
                    ],
                ],
            ],
            #5
            [
                (object) [
                    'type' => (string) Type::BATCH(),
                    'children' => [
                        (object) [
                            'type' => (string) Type::JOB(),
                            'name' => 'valid',
                        ],
                    ],
                ],
            ],
            #6
            [
                (object) [
                    'type' => (string) Type::JOB(),
                    'name' => 'valid',
                    'children' => [
                        (object) [
                            'type' => (string) Type::JOB(),
                            'name' => 'valid',
                        ],
                        (object) [
                            'type' => (string) Type::JOB(),
                            'name' => 'valid',
                        ],
                    ],
                ],
            ],

        ];
    }

    public static function provideValidFilter(): array
    {
        return [
            ['id=00000000-0000-0000-0000-000000000000'],
            [
                http_build_query([
                    'id' => [
                        '00000000-0000-0000-0000-000000000000',
                        '00000000-1111-1111-1111-111111111111',
                    ],
                ]),
            ],
            ['externalId=00000000-0000-0000-0000-000000000000'],
            [
                http_build_query([
                    'externalId' => [
                        '00000000-0000-0000-0000-000000000000',
                        '00000000-1111-1111-1111-111111111111',
                    ],
                ]),
            ],
            ['name=valid'],
            [http_build_query(['name' => ['validA', 'validB']])],
            ['status=failure'],
            [http_build_query(['status' => ['waiting', 'scheduled', 'running', 'complete', 'failure', 'cancelled']])],
        ];
    }

    public static function provideInvalidFilter(): array
    {
        return [
            ['id=000'],
            [http_build_query(['id' => ['000', '111']])],
            ['externalId=000'],
            [http_build_query(['externalId' => ['000', '11a']])],
            ['name=aa'],
            [http_build_query(['name' => ['aa', 'bb']])],
            ['status=undefined'],
            [http_build_query(['status' => ['undefined']])],
        ];
    }

    public function provideValidRoute(): array
    {
        return [
            [
                (object) [
                    'jobName' => 'abc',
                    'queueName' => 'abc',
                    'replyTo' => 'abc',
                ],
            ],
            [
                (object) [
                    'jobName' => 'a.bc',
                    'queueName' => 'abc',
                    'replyTo' => 'abc',
                ],
            ],
            [
                (object) [
                    'jobName' => 'a.b1',
                    'queueName' => 'abc',
                    'replyTo' => 'abc',
                ],
            ],
            [
                (object) [
                    'jobName' => 'a.b_c',
                    'queueName' => 'abc',
                    'replyTo' => 'abc',
                ],
            ],
            [
                (object) [
                    'jobName' => str_repeat('a', 20),
                    'queueName' => str_repeat('a', 20),
                    'replyTo' => str_repeat('a', 20),
                ],
            ],
            [
                [
                    (object) [
                        'jobName' => str_repeat('a', 20),
                        'queueName' => str_repeat('a', 20),
                        'replyTo' => str_repeat('a', 20),
                    ],
                ],
            ],
        ];
    }

    public function provideInvalidRoute(): array
    {
        return [
            [
                (object) [
                    'jobName' => 'jobName',
                ],
            ],
            [
                (object) [
                    'queueName' => 'queueName',
                ],
            ],
            [
                (object) [
                    'replyTo' => 'replyTo',
                ],
            ],
            [
                (object) [
                    'jobName' => 'jobName',
                    'queueName' => 'queueName',
                ],
            ],
            [
                (object) [
                    'queueName' => 'queueName',
                    'replyTo' => 'replyTo',
                ],
            ],
            [
                (object) [
                    'jobName' => 'jobName',
                    'replyTo' => 'replyTo',
                ],
            ],
            [
                (object) [
                    'jobName' => 'jo',
                    'queueName' => 'queueName',
                    'replyTo' => 'replyTo',
                ],
            ],
            [
                (object) [
                    'jobName' => 'job',
                    'queueName' => 'aa',
                    'replyTo' => 'replyTo',
                ],
            ],
            [
                (object) [
                    'jobName' => 'abc',
                    'queueName' => 'abc',
                    'replyTo' => 'ab',
                ],
            ],
            [
                (object) [
                    'jobName' => str_repeat('a', 21),
                    'queueName' => str_repeat('a', 21),
                    'replyTo' => str_repeat('a', 21),
                ],
            ],
            [
                (object) [
                    'jobName' => 'Abc',
                    'queueName' => 'abc',
                    'replyTo' => 'abc',
                ],
            ],
            [
                (object) [
                    'jobName' => 'a-c',
                    'queueName' => 'abc',
                    'replyTo' => 'abc',
                ],
            ],
            [
                (object) [],
            ],
        ];
    }
}
