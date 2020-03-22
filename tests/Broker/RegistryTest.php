<?php

namespace Abc\Job\Tests\Broker;

use Abc\Job\Broker\BrokerInterface;
use Abc\Job\Broker\Registry;
use PHPUnit\Framework\TestCase;

class RegistryTest extends TestCase
{
    public function testDuplicateAdd()
    {
        $broker = $this->createMock(BrokerInterface::class);

        $this->expectException(\InvalidArgumentException::class);

        (new Registry())->add('name', $broker)->add('name', $broker);
    }

    public function testAdd()
    {
        $broker = $this->createMock(BrokerInterface::class);

        $this->assertSame($broker, (new Registry())->add('name', $broker)->get('name'));
    }

    public function testExists()
    {
        $broker = $this->createMock(BrokerInterface::class);

        $this->assertTrue((new Registry())->add('name', $broker)->exists('name'));

        $this->assertFalse((new Registry())->exists('name'));
    }
}
