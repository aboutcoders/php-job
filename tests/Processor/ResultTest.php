<?php

namespace Abc\Job\Tests\Processor;

use Abc\Job\Processor\Result;
use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{
    /**
     * @param array $arguments
     * @param $expectedStatus
     * @param $expectedOutput
     * @dataProvider providerConstructorArguments
     */
    public function testConstructor(array $arguments, $expectedStatus, $expectedOutput)
    {
        $result = new Result(...$arguments);

        $this->assertEquals($expectedStatus, $result->getStatus());
        $this->assertEquals($expectedOutput, $result->getOutput());
    }

    public static function providerConstructorArguments(): array
    {
        return [
            [[Result::COMPLETE], Result::COMPLETE, null],
            [[Result::COMPLETE, null], Result::COMPLETE, null],
            [[Result::COMPLETE, 'someOutput'], Result::COMPLETE, 'someOutput'],
        ];
    }
}
