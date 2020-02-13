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

    public function testWithInvalidJson()
    {
        $this->expectException(InvalidJsonException::class);

        $this->subject->validate('json', Job::class);
    }

    /**
     * @dataProvider provideValidJob
     * @param \stdClass $job
     */
    public function testValidJob(\stdClass $job)
    {
        $json = json_encode($job);

        $errors = $this->subject->validate($json, Job::class);

        $this->assertEmpty($errors);

        $job = Job::fromJson($json);
    }

    /**
     * @dataProvider provideInvalidJob
     * @param \stdClass $job
     */
    public function testInvalidJob(\stdClass $job)
    {
        $json = json_encode($job);
        $errors = $this->subject->validate($json, Job::class);
        $this->assertNotEmpty($errors);
    }

    /**
     * @dataProvider provideValidCronJob
     * @param \stdClass $job
     */
    public function testValidCronJob(\stdClass $job)
    {
        $json = json_encode($job);
        $errors = $this->subject->validate($json, CronJob::class);
        $this->assertEmpty($errors);
    }

    /**
     * @dataProvider provideInvalidCronJob
     * @param \stdClass $job
     */
    public function testInvalidCronJob(\stdClass $job)
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
            #0 minimal job
            [
                (object) [
                    'type' => (string) Type::JOB(),
                    'name' => 'valid',
                ],
            ],
            #0 job with input is null
            [
                (object) [
                    'type' => (string) Type::JOB(),
                    'name' => 'valid',
                    'input' => null,
                ],
            ],
            #0 job with externalId is null
            [
                (object) [
                    'type' => (string) Type::JOB(),
                    'name' => 'valid',
                    'externalId' => null,
                ],
            ],
            #1 minimal sequence
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
            #2 minimal batch
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
            # collection with name
            [
                (object) [
                    'type' => (string) Type::BATCH(),
                    'name' => 'valid',
                    'children' => [
                        (object) [
                            'type' => (string) Type::JOB(),
                            'name' => 'valid',
                        ],
                    ],
                ],
            ],
            # collection with null values
            [
                (object) [
                    'type' => (string) Type::BATCH(),
                    'name' => null,
                    'externalId' => null,
                    'children' => [
                        (object) [
                            'type' => (string) Type::JOB(),
                            'name' => 'valid',
                        ],
                    ],
                ],
            ],
            #3 job with empty children array
            [
                (object) [
                    'type' => (string) Type::JOB(),
                    'name' => 'valid',
                    'input' => 'someInput',
                    'allowFailure' => false,
                    'externalId' => Uuid::uuid4(),
                    'children' => [],
                ],
            ],
            #4 Sequence with two children
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
            #5 Batch with two children
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
            #6 nested collection
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
                    'name' => str_repeat('a', 26),
                ],
            ],
            #4
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
            #0 minimal cronjob
            [
                (object) [
                    'schedule' => '* * * * *',
                    'type' => (string) Type::JOB(),
                    'name' => 'valid',
                ],
            ],
            #1 cronjob with input is null
            [
                (object) [
                    'schedule' => '* * * * *',
                    'type' => (string) Type::JOB(),
                    'name' => 'valid',
                    'input' => null,
                ],
            ],
            #2 cronjob with externalId is null
            [
                (object) [
                    'schedule' => '* * * * *',
                    'type' => (string) Type::JOB(),
                    'name' => 'valid',
                    'externalId' => null,
                ],
            ],
            #3 job with empty children array
            [
                (object) [
                    'schedule' => '* * * * *',
                    'type' => (string) Type::JOB(),
                    'name' => 'valid',
                    'children' => [],
                ],
            ],
            #4 Sequence
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
                    ],
                ],
            ],
            #4 Batch
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
            #5 collection with name
            [
                (object) [
                    'schedule' => '* * * * *',
                    'type' => (string) Type::BATCH(),
                    'name' => 'valid',
                    'children' => [
                        (object) [
                            'type' => (string) Type::JOB(),
                            'name' => 'valid',
                        ],
                    ],
                ],
            ],
            #6 collection with null values
            [
                (object) [
                    'schedule' => '* * * * *',
                    'type' => (string) Type::BATCH(),
                    'name' => null,
                    'externalId' => null,
                    'children' => [
                        (object) [
                            'type' => (string) Type::JOB(),
                            'name' => 'valid',
                        ],
                    ],
                ],
            ],
            #7 nested collection
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
                    'name' => str_repeat('a', 26),
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
                    'children' => [],
                ],
            ],
            #7
            [
                (object) [
                    'schedule' => '* * * * *',
                    'type' => (string) Type::BATCH(),
                    'children' => [],
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
            /*['ids=00000000-0000-0000-0000-000000000000'],
            [
                'ids='.implode(',', [
                    '00000000-0000-0000-0000-000000000000',
                    '00000000-1111-1111-1111-111111111111',
                ]),
            ],
            ['externalIds=00000000-0000-0000-0000-000000000000'],
            [
                'externalIds='.implode(',', [
                    '00000000-0000-0000-0000-000000000000',
                    '00000000-1111-1111-1111-111111111111',
                ]),
            ],*/ ['names=valid'],
            ['names=validA,validB'],
            #['status=failure'],
            #['status='.implode(',', ['waiting', 'scheduled', 'running', 'complete', 'failure', 'cancelled'])],
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
                    'name' => str_repeat('a', 25),
                    'queue' => str_repeat('a', 50),
                    'replyTo' => str_repeat('a', 50),
                ],
            ],
            [
                [
                    (object) [
                        'name' => str_repeat('a', 25),
                        'queue' => str_repeat('a', 50),
                        'replyTo' => str_repeat('a', 50),
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
                    'name' => str_repeat('a', 26),
                    'queue' => str_repeat('a', 50),
                    'replyTo' => str_repeat('a', 50),
                ],
            ],
            [
                (object) [
                    'name' => str_repeat('a', 20),
                    'queue' => str_repeat('a', 51),
                    'replyTo' => str_repeat('a', 50),
                ],
            ],
            [
                (object) [
                    'name' => str_repeat('a', 20),
                    'queue' => str_repeat('a', 50),
                    'replyTo' => str_repeat('a', 51),
                ],
            ],
            [
                (object) [],
            ],
        ];
    }
}
