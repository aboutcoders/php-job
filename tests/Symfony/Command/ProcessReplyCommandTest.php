<?php

namespace Abc\Job\Tests\Symfony\Command;

use Abc\Job\Symfony\Command\ProcessReplyCommand;
use Enqueue\Symfony\Consumption\ConfigurableConsumeCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;

class ProcessReplyCommandTest extends TestCase
{
    public function testGetName()
    {
        $consumeCommand = $this->createMock(ConfigurableConsumeCommand::class);

        $inputDefinition = new InputDefinition();
        $inputDefinition->addArgument(new InputArgument('queues'));

        $consumeCommand->expects($this->any())->method('getDefinition')->willReturn($inputDefinition);

        $subject = new ProcessReplyCommand($consumeCommand);

        $this->assertSame('abc:reply:process', $subject->getName());
        $this->assertContains('abc:process:reply', $subject->getAliases());
    }
}
