<?php

namespace Abc\Job\Tests\Validator;

use Abc\Job\Broker\Route;
use Abc\Job\CronJob;
use Abc\Job\InvalidJsonException;
use Abc\Job\JobFilter;
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

    public function testWithInvalidJson() {

        $this->expectException(InvalidJsonException::class);

        $this->subject->validate('json', Job::class);
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
     * @dataProvider provideValidCronJob
     */
    public function testValidCronJob($job)
    {
        $json = json_encode($job);
        $errors = $this->subject->validate($json, CronJob::class);
        $this->assertEmpty($errors);
    }

    /**
     * @dataProvider provideInvalidCronJob
     */
    public function testInvalidCronJob($job)
    {
        $json = json_encode($job);
        $errors = $this->subject->validate($json, CronJob::class);
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

        $errors = $this->subject->validate($json, JobFilter::class);
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

        $errors = $this->subject->validate($json, JobFilter::class);
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

    public static function provideValidCronJob(): array
    {
        return [
            #0
            [
                (object) [
                    'schedule' => '* * * * *',
                    'type' => (string) Type::JOB(),
                    'name' => 'valid',
                ],
            ],
            #1
            [
                (object) [
                    'schedule' => '* * * * *',
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
                    'schedule' => '* * * * *',
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
                    'schedule' => '* * * * *',
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

    public static function provideInvalidCronJob(): array
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
                    'schedule' => '* * * * *',
                ],
            ],
            #3
            [
                (object) [
                    'schedule' => '* * * * *',
                    'type' => (string) Type::JOB(),
                    'name' => str_repeat('a', 2),
                ],
            ],
            #4
            [
                (object) [
                    'schedule' => '* * * * *',
                    'type' => (string) Type::JOB(),
                    'name' => str_repeat('a', 21),
                ],
            ],
            #5
            [
                (object) [
                    'type' => (string) Type::JOB(),
                    'name' => 'valid',
                ],
            ],
            #6
            [
                (object) [
                    'schedule' => '* * * * *',
                    'type' => (string) Type::SEQUENCE(),
                    'children' => [
                        (object) [
                            'type' => (string) Type::JOB(),
                            'name' => 'valid',
                        ],
                    ],
                ],
            ],
            #7
            [
                (object) [
                    'schedule' => '* * * * *',
                    'type' => (string) Type::BATCH(),
                    'children' => [
                        (object) [
                            'type' => (string) Type::JOB(),
                            'name' => 'valid',
                        ],
                    ],
                ],
            ],
            #8
            [
                (object) [
                    'schedule' => '* * * * *',
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
                    'name' => 'abc',
                    'queue' => 'abc',
                    'replyTo' => 'abc',
                ],
            ],
            [
                (object) [
                    'name' => 'a.bc',
                    'queue' => 'abc',
                    'replyTo' => 'abc',
                ],
            ],
            [
                (object) [
                    'name' => 'a.b1',
                    'queue' => 'abc',
                    'replyTo' => 'abc',
                ],
            ],
            [
                (object) [
                    'name' => 'aA.-_1',
                    'queue' => 'abc',
                    'replyTo' => 'abc',
                ],
            ],
            [
                (object) [
                    'name' => 'a.b_c',
                    'queue' => 'abc',
                    'replyTo' => 'abc',
                ],
            ],
            [
                (object) [
                    'name' => str_repeat('a', 20),
                    'queue' => str_repeat('a', 20),
                    'replyTo' => str_repeat('a', 20),
                ],
            ],
            [
                [
                    (object) [
                        'name' => str_repeat('a', 20),
                        'queue' => str_repeat('a', 20),
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
                    'name' => 'name',
                ],
            ],
            [
                (object) [
                    'queue' => 'queue',
                ],
            ],
            [
                (object) [
                    'replyTo' => 'replyTo',
                ],
            ],
            [
                (object) [
                    'name' => 'name',
                    'queue' => 'queue',
                ],
            ],
            [
                (object) [
                    'queue' => 'queue',
                    'replyTo' => 'replyTo',
                ],
            ],
            [
                (object) [
                    'name' => 'name',
                    'replyTo' => 'replyTo',
                ],
            ],
            [
                (object) [
                    'name' => 'jo',
                    'queue' => 'queue',
                    'replyTo' => 'replyTo',
                ],
            ],
            [
                (object) [
                    'name' => 'job',
                    'queue' => 'aa',
                    'replyTo' => 'replyTo',
                ],
            ],
            [
                (object) [
                    'name' => 'abc',
                    'queue' => 'abc',
                    'replyTo' => 'ab',
                ],
            ],
            [
                (object) [
                    'name' => str_repeat('a', 21),
                    'queue' => str_repeat('a', 21),
                    'replyTo' => str_repeat('a', 21),
                ],
            ],
            [
                (object) [],
            ],
        ];
    }
}
