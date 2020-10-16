<?php

namespace Abc\Job\Tests;

use Abc\Job\JobManager;
use Abc\Job\JobServer;
use Abc\Job\Model\Job;
use Abc\Job\Model\JobInterface;
use Abc\Job\Processor\Reply;
use Abc\Job\ReplyProcessor;
use Abc\Job\Result;
use Abc\Job\Status;
use Abc\Job\Type;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ReplyProcessorTest extends TestCase
{
    /**
     * @var JobServer|MockObject
     */
    private $jobServer;

    /**
     * @var  JobManager|MockObject
     */
    private $jobManager;

    /**
     * @var ReplyProcessor
     */
    private $subject;

    public function setUp(): void
    {
        $this->jobServer = $this->createMock(JobServer::class);
        $this->jobManager = $this->createMock(JobManager::class);
        $this->subject = new ReplyProcessor($this->jobServer, $this->jobManager, new NullLogger());
    }

    /**
     * @dataProvider provideData
     */
    public function testProcess(Reply $reply, Job $job, Job $expectedJob)
    {
        $this->jobManager->expects($this->once())->method('find')->with('someJobId')->willReturn($job);

        $this->jobManager->expects($this->once())->method('save')->with($this->equalTo($expectedJob));

        $this->jobServer->expects($this->any())->method('trigger')->willReturnCallback(function(JobInterface $job) {
            $job->setStatus(Status::SCHEDULED);
            return new Result($job);
        });

        $this->subject->process($reply);

        /*echo "actual:";
        echo $this->printJob($job->getRoot());
        echo "\n";

        echo "expected:";
        echo $this->printJob($expectedJob->getRoot());
        exit;*/
    }

    public static function provideData(): array
    {
        return [
            // 0 single job
            [
                new Reply('someJobId', Status::RUNNING, 'someOutput'),
                self::createJob(Type::JOB(), Status::SCHEDULED, null, 0, null),
                self::createJob(Type::JOB(), Status::RUNNING, 'someOutput', 0, null),
            ],
            // 1 single job
            [
                new Reply('someJobId', Status::COMPLETE, 'someOutput', 0.01, 10000),
                self::createJob(Type::JOB(), Status::WAITING, null, 0, null),
                self::createJob(Type::JOB(), Status::COMPLETE, 'someOutput', 0.01, new \DateTime('@10000')),
            ],
            // 2 single job
            [
                new Reply('someJobId', Status::FAILED, 'someOutput', 0.01, 10000),
                self::createJob(Type::JOB(), Status::WAITING, null, 0, null),
                self::createJob(Type::JOB(), Status::FAILED, 'someOutput', 0.01, new \DateTime('@10000')),
            ],
            // 3 Batch with single child job
            [
                new Reply('someJobId', Status::RUNNING, null, 0, 1000),
                self::createChildJob(
                    self::createJob(Type::BATCH(), Status::SCHEDULED, null, 0, null),
                    self::createJob(Type::JOB(), Status::SCHEDULED, null, 0, null)
                ),
                self::createChildJob(
                    self::createJob(Type::BATCH(), Status::RUNNING, null, 0, null),
                    self::createJob(Type::JOB(), Status::RUNNING, null, 0, null)
                ),
            ],
            // 4 Batch with single child job (allowFailure=false) that failed
            [
                new Reply('someJobId', Status::FAILED, null, 0, 1000),
                self::createChildJob(
                    self::createJob(Type::BATCH(), Status::RUNNING, null, 0, null),
                    self::createJob(Type::JOB(), Status::RUNNING, null, 0, null)
                ),
                self::createChildJob(
                    self::createJob(Type::BATCH(), Status::FAILED, null, 0, new \DateTime('@1000')),
                    self::createJob(Type::JOB(), Status::FAILED, null, 0, new \DateTime('@1000'))
                ),
            ],
            // 5 Batch with single child job (allowFailure=true) that failed
            [
                new Reply('someJobId', Status::FAILED, null, 0, 1000),
                self::createChildJob(
                    self::createJob(Type::BATCH(), Status::RUNNING, null, 0, null),
                    self::createJob(Type::JOB(), Status::RUNNING, null, 0, null, true)
                ),
                self::createChildJob(
                    self::createJob(Type::BATCH(), Status::COMPLETE, null, 0, new \DateTime('@1000')),
                    self::createJob(Type::JOB(), Status::FAILED, null, 0, new \DateTime('@1000'), true)
                ),
            ],
            // 6 Sequence with single child job
            [
                new Reply('someJobId', Status::RUNNING, null, 0, 1000),
                self::createChildJob(
                    self::createJob(Type::SEQUENCE(), Status::SCHEDULED, null, 0, null),
                    self::createJob(Type::JOB(), Status::SCHEDULED, null, 0, null)
                ),
                self::createChildJob(
                    self::createJob(Type::SEQUENCE(), Status::RUNNING, null, 0, null),
                    self::createJob(Type::JOB(), Status::RUNNING, null, 0, null)
                ),
            ],
            // 7 Sequence with single child job (allowFailure=false)
            [
                new Reply('someJobId', Status::FAILED, null, 0, 1000),
                self::createChildJob(
                    self::createJob(Type::SEQUENCE(), Status::RUNNING, null, 0, null),
                    self::createJob(Type::JOB(), Status::RUNNING, null, 0, null)
                ),
                self::createChildJob(
                    self::createJob(Type::SEQUENCE(), Status::FAILED, null, 0, new \DateTime('@1000')),
                    self::createJob(Type::JOB(), Status::FAILED, null, 0, new \DateTime('@1000'))
                ),
            ],
            // 8 Sequence with single child job (allowFailure=true)
            [
                new Reply('someJobId', Status::FAILED, null, 0, 1000),
                self::createChildJob(
                    self::createJob(Type::SEQUENCE(), Status::RUNNING, null, 0, null),
                    self::createJob(Type::JOB(), Status::RUNNING, null, 0, null, true)
                ),
                self::createChildJob(
                    self::createJob(Type::SEQUENCE(), Status::COMPLETE, null, 0, new \DateTime('@1000')),
                    self::createJob(Type::JOB(), Status::FAILED, null, 0, new \DateTime('@1000'), true)
                ),
            ],
            // 9 Sequence with child job with right neighbours
            [
                new Reply('someJobId', Status::FAILED, null, 0, 1000),
                self::createChildJobWithNeighbours(
                    self::createJob(Type::SEQUENCE(), Status::RUNNING, null, 0, null),
                    self::createJob(Type::JOB(), Status::RUNNING, null, 0, null),
                    [],
                    [
                        self::createJob(Type::JOB(), Status::WAITING, null, 0, null),
                    ]
                ),
                self::createChildJobWithNeighbours(
                    self::createJob(Type::SEQUENCE(), Status::FAILED, null, 0, new \DateTime('@1000')),
                    self::createJob(Type::JOB(), Status::FAILED, null, 0, new \DateTime('@1000')),
                    [],
                    [
                        self::createJob(Type::JOB(), Status::CANCELLED, null, 0, null),
                    ]
                ),
            ],
            // 10 Sequence with child job with right neighbours
            [
                new Reply('someJobId', Status::COMPLETE, null, 0, 1000),
                self::createChildJobWithNeighbours(
                    self::createJob(Type::SEQUENCE(), Status::RUNNING, null, 0, null),
                    self::createJob(Type::JOB(), Status::RUNNING, null, 0, null),
                    [],
                    [
                        self::createJob(Type::JOB(), Status::WAITING, null, 0, null),
                    ]
                ),
                self::createChildJobWithNeighbours(
                    self::createJob(Type::SEQUENCE(), Status::RUNNING, null, 0, null),
                    self::createJob(Type::JOB(), Status::COMPLETE, null, 0, new \DateTime('@1000')),
                    [],
                    [
                        self::createJob(Type::JOB(), Status::SCHEDULED, null, 0, null),
                    ]
                ),
            ],
            // 11 Batch with child job with right neighbours
            [
                new Reply('someJobId', Status::FAILED, null, 0, 1000),
                self::createChildJobWithNeighbours(
                    self::createJob(Type::BATCH(), Status::RUNNING, null, 0, null),
                    self::createJob(Type::JOB(), Status::RUNNING, null, 0, null),
                    [],
                    [
                        self::createJob(Type::JOB(), Status::RUNNING, null, 0, null),
                    ]
                ),
                self::createChildJobWithNeighbours(
                    self::createJob(Type::BATCH(), Status::FAILED, null, 0, new \DateTime('@1000')),
                    self::createJob(Type::JOB(), Status::FAILED, null, 0, new \DateTime('@1000')),
                    [],
                    [
                        self::createJob(Type::JOB(), Status::RUNNING, null, 0, null),
                    ]
                ),
            ],
            // 12 Batch with child job with right neighbours
            [
                new Reply('someJobId', Status::COMPLETE, null, 0, 1000),
                self::createChildJobWithNeighbours(
                    self::createJob(Type::BATCH(), Status::RUNNING, null, 0, null),
                    self::createJob(Type::JOB(), Status::RUNNING, null, 0, null),
                    [],
                    [
                        self::createJob(Type::JOB(), Status::RUNNING, null, 0, null),
                    ]
                ),
                self::createChildJobWithNeighbours(
                    self::createJob(Type::BATCH(), Status::RUNNING, null, 0, null),
                    self::createJob(Type::JOB(), Status::COMPLETE, null, 0, new \DateTime('@1000')),
                    [],
                    [
                        self::createJob(Type::JOB(), Status::RUNNING, null, 0, null),
                    ]
                ),
            ],
            // 13 Batch with child job (allowFailure=true) with right neighbours
            [
                new Reply('someJobId', Status::FAILED, null, 0, 1000),
                self::createChildJobWithNeighbours(
                    self::createJob(Type::BATCH(), Status::RUNNING, null, 0, null),
                    self::createJob(Type::JOB(), Status::RUNNING, null, 0, null, true),
                    [],
                    [
                        self::createJob(Type::JOB(), Status::RUNNING, null, 0, null),
                    ]
                ),
                self::createChildJobWithNeighbours(
                    self::createJob(Type::BATCH(), Status::RUNNING, null, 0, null),
                    self::createJob(Type::JOB(), Status::FAILED, null, 0, new \DateTime('@1000'), true),
                    [],
                    [
                        self::createJob(Type::JOB(), Status::RUNNING, null, 0, null),
                    ]
                ),
            ],
            // 14
            // S
            // |
            // S
            // |
            // X
            [
                new Reply('someJobId', Status::COMPLETE, null, 0, 1000),
                self::createChildJobWithNeighbours(
                    self::createChildJobWithNeighbours(
                        self::createJob(Type::SEQUENCE(), Status::RUNNING, null, 0, null),
                        self::createJob(Type::SEQUENCE(), Status::RUNNING, null, 0, null),
                        [],
                        []
                    ),
                    self::createJob(Type::JOB(), Status::RUNNING, null, 0, null),
                    [],
                    []
                ),
                self::createChildJobWithNeighbours(
                    self::createChildJobWithNeighbours(
                        self::createJob(Type::SEQUENCE(), Status::COMPLETE, null, 0, new \DateTime('@1000')),
                        self::createJob(Type::SEQUENCE(), Status::COMPLETE, null, 0, new \DateTime('@1000')),
                        [],
                        []
                    ),
                    self::createJob(Type::JOB(), Status::COMPLETE, null, 0, new \DateTime('@1000')),
                    [],
                    []
                ),
            ],
            // 15 Sequence with child job type sequence with right neighbours
            // S
            // |
            // S-J
            // |
            // X
            [
                new Reply('someJobId', Status::COMPLETE, null, 0, 1000),
                self::createChildJob(
                    self::createChildJobWithNeighbours(
                        self::createJob(Type::SEQUENCE(), Status::RUNNING, null, 0, null),
                        self::createJob(Type::SEQUENCE(), Status::RUNNING, null, 0, null),
                        [],
                        [
                            self::createJob(Type::JOB(), Status::WAITING, null, 0, null),
                        ]
                    ),
                    self::createJob(Type::JOB(), Status::RUNNING, null, 0, null)
                ),
                self::createChildJob(
                    self::createChildJobWithNeighbours(
                        self::createJob(Type::SEQUENCE(), Status::RUNNING, null, 0, null),
                        self::createJob(Type::SEQUENCE(), Status::COMPLETE, null, 0, new \DateTime('@1000')),
                        [],
                        [
                            self::createJob(Type::JOB(), Status::SCHEDULED, null, 0, null),
                        ]
                    ),
                    self::createJob(Type::JOB(), Status::COMPLETE, null, 0, new \DateTime('@1000'))
                ),
            ],
            // 16
            // S
            // |
            // J-S-J
            //   |
            //   X
            [
                new Reply('someJobId', Status::FAILED, null, 0, 1000),
                self::createChildJob(
                    self::createChildJobWithNeighbours(
                        self::createJob(Type::SEQUENCE(), Status::RUNNING, null, 0, null),
                        self::createJob(Type::SEQUENCE(), Status::RUNNING, null, 0, null),
                        [
                            self::createJob(Type::JOB(), Status::COMPLETE, null, 0, null),
                        ],
                        [
                            self::createJob(Type::JOB(), Status::WAITING, null, 0, null),
                        ]
                    ),
                    self::createJob(Type::JOB(), Status::RUNNING, null, 0, null)
                ),
                self::createChildJob(
                    self::createChildJobWithNeighbours(
                        self::createJob(Type::SEQUENCE(), Status::FAILED, null, 0, new \DateTime('@1000')),
                        self::createJob(Type::SEQUENCE(), Status::FAILED, null, 0, new \DateTime('@1000')),
                        [
                            self::createJob(Type::JOB(), Status::COMPLETE, null, 0, null),
                        ],
                        [
                            self::createJob(Type::JOB(), Status::CANCELLED, null, 0, null),
                        ]
                    ),
                    self::createJob(Type::JOB(), Status::FAILED, null, 0, new \DateTime('@1000'))
                ),
            ],
        ];
    }

    private static function createJob(
        $type,
        $status,
        $output,
        $processingTime,
        $completedAt,
        $allowFailure = false
    ): JobInterface {
        $job = new Job();
        $job->setType($type);
        $job->setId('jobId');
        $job->setStatus($status);
        $job->setOutput($output);
        $job->setProcessingTime($processingTime);
        $job->setCompletedAt($completedAt);
        $job->setAllowFailure($allowFailure);

        return $job;
    }

    private static function createChildJobWithNeighbours(
        JobInterface $parent,
        JobInterface $child,
        array $leftNeighbours,
        array $rightNeighbours
    ) {
        foreach ($leftNeighbours as $leftNeighbour) {
            $parent->addChild($leftNeighbour);
        }

        $parent->addChild($child);

        foreach ($rightNeighbours as $rightNeighbour) {
            $parent->addChild($rightNeighbour);
        }

        return $child;
    }

    private static function createChildJob(JobInterface $parent, JobInterface $child)
    {
        $parent->addChild($child);

        return $child;
    }

    private function printJob(JobInterface $job, &$output = '', $indent = 0)
    {
        $output .= "\n".sprintf(
                '%s%s %s %s',
                0 == $indent ? '' : str_repeat(' ', $indent * 4),
                null != $job->hasParent() && Type::SEQUENCE() == $job->getParent()->getType() ? $job->getPosition(
                    ).'.' : '',
                $job->getType().':',
                $job->getStatus()
            );
        $indent++;
        foreach ($job->getChildren() as $child) {
            $this->printJob($child, $output, $indent);
        }

        return $output;
    }
}
