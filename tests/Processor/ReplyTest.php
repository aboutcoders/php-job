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
            [[Status::COMPLETE], Status::COMPLETE, null, 0.0],
        ];
    }

    /**
     * @param string $json
     * @dataProvider provideValidJson
     */
    public function testFromJson(string $json, $status)
    {
        $reply = Reply::fromJson($json);

        $this->assertInstanceOf(Reply::class, $reply);
        $this->assertEquals($status, $reply->getStatus());
    }

    public static function provideValidJson(): array
    {
        return [
            [
                json_encode((object) [
                    'status' => Status::RUNNING,
                ]),
                Status::RUNNING,
            ],
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
