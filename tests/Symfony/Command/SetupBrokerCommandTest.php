<?php

namespace Abc\Job\Tests\Symfony\Command;

use Abc\Job\Broker\BrokerInterface;
use Abc\Job\Broker\RegistryInterface;
use Abc\Job\Symfony\Command\SetupBrokerCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\NullOutput;

class SetupBrokerCommandTest extends TestCase
{
    /**
     * @var RegistryInterface|MockObject
     */
    private $registry;

    public function setUp(): void
    {
        $this->registry = $this->createMock(RegistryInterface::class);
    }

    public function testGetName()
    {
        $this->assertEquals('abc:broker:setup', (new SetupBrokerCommand($this->registry))->getName());
    }

    public function testRun()
    {
        $broker = $this->createMock(BrokerInterface::class);

        $this->registry->expects($this->any())->method('exists')->with('default')->willReturn(true);
        $this->registry->expects($this->any())->method('get')->willReturn($broker);

        $broker->expects($this->once())->method('setup')->with($this->isInstanceOf(ConsoleLogger::class));

        $this->assertSame(
            0,
            (new SetupBrokerCommand($this->registry))->run(new ArrayInput([]), new NullOutput())
        );
    }

    public function testRunWithName()
    {
        $broker = $this->createMock(BrokerInterface::class);

        $this->registry->expects($this->any())->method('exists')->with('someName')->willReturn(true);
        $this->registry->expects($this->any())->method('get')->willReturn($broker);

        $broker->expects($this->once())->method('setup')->with($this->isInstanceOf(ConsoleLogger::class));

        $this->assertSame(
            0,
            (new SetupBrokerCommand($this->registry))->run(
                new ArrayInput(
                    [
                        // pass arguments to the helper
                        'name' => 'someName',
                    ]
                ),
                new NullOutput()
            )
        );
    }

    public function testRunWithBrokerNotExits()
    {
        $this->registry->expects($this->any())->method('exists')->with('someName')->willReturn(false);

        $this->expectException(\LogicException::class);

        (new SetupBrokerCommand($this->registry))->run(
            new ArrayInput(
                [
                    // pass arguments to the helper
                    'name' => 'someName',
                ]
            ),
            new NullOutput()
        );
    }
}
