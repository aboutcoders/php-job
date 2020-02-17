<?php

namespace Abc\Job\Tests\Processor;

use Abc\Job\Processor\Reply;
use Abc\Job\Status;
use PHPUnit\Framework\TestCase;

class ReplyTest extends TestCase
{
    /**
     * @param array $arguments
     * @param $status
     * @param $output
     * @param $processingTime
     * @param $createdTimestamp
     * @dataProvider getConstructorArgs
     */
    public function testConstructor(array $arguments, $status, $output, $processingTime, $createdTimestamp = null)
    {
        $reply = new Reply(...$arguments);
        $this->assertEquals($status, $reply->getStatus());
        $this->assertSame($output, $reply->getOutput());
        $this->assertSame($processingTime, $reply->getProcessingTime());

        if (null != $createdTimestamp) {
            $this->assertEquals($createdTimestamp, $reply->getCreatedTimestamp());
        }
    }

    public static function getConstructorArgs(): array
    {
        return [
            [['someJobId', Status::COMPLETE], Status::COMPLETE, null, null],
            [['someJobId', Status::COMPLETE, 'someOutput', 1.0], Status::COMPLETE, 'someOutput', 1.0],
        ];
    }

    /**
     * @param string $json
     * @param Reply $expectedReply
     * @dataProvider provideValidJson
     */
    public function testFromJson(string $json, Reply $expectedReply)
    {
        $reply = Reply::fromJson($json);

        $this->assertInstanceOf(Reply::class, $reply);
        $this->assertEquals($expectedReply, $reply);
    }

    public static function provideValidJson(): array
    {
        return [
            [
                json_encode((object) [
                    'jobId' => 'someId',
                    'status' => Status::RUNNING,
                    'createdTimestamp' => 100
                ]),
                new Reply('someId', Status::RUNNING, null, null, 100)
            ],
            [
                json_encode((object) [
                    'jobId' => 'someId',
                    'status' => Status::RUNNING,
                    'output' => 'someOutput',
                    'processingTime' => 1.0,
                    'createdTimestamp' => 100
                ]),
                new Reply('someId', Status::RUNNING, 'someOutput', 1.0, 100)
            ]
        ];
    }

    public static function provideInvalidJson(): array
    {
        return [
            [''],
            [json_encode('foobar')],
        ];
    }
}
