<?php

namespace Abc\Job\Tests\Symfony\Command;

use Abc\Job\Client\RouteClient;
use Abc\Job\Interop\JobConsumer;
use Abc\Job\Processor\ProcessorRegistry;
use Abc\Job\Symfony\Command\ProcessJobCommand;
use Enqueue\Symfony\Consumption\ConfigurableConsumeCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputDefinition;

class ProcessJobCommandTest extends TestCase
{
    /**
     * @var ProcessorRegistry
     */
    private $processorRegistryMock;

    /**
     * @var RouteClient
     */
    private $routeClientMock;

    /**
     * @var JobConsumer
     */
    private $jobConsumerMock;

    /**
     * @var ConfigurableConsumeCommand
     */
    private $consumeCommandMock;

    /**
     * @var InputDefinition
     */
    private $consumeCommandInputDefinition;

    /**
     * @var ProcessJobCommand
     */
    private $subject;

    public function setUp(): void
    {
        $this->routeClientMock = $this->createMock(RouteClient::class);
        $this->processorRegistryMock = $this->createMock(ProcessorRegistry::class);
        $this->jobConsumerMock = $this->createMock(JobConsumer::class);
        $this->consumeCommandMock = $this->createMock(ConfigurableConsumeCommand::class);

        $this->consumeCommandInputDefinition = new InputDefinition();

        $this->consumeCommandMock->expects($this->any())->method('getDefinition')->willReturn($this->consumeCommandInputDefinition);

        $this->subject = new ProcessJobCommand($this->processorRegistryMock, $this->routeClientMock, $this->jobConsumerMock, $this->consumeCommandMock);
    }

    public function testGetName()
    {
        $this->assertSame('abc:process:job', $this->subject->getName());
    }
}
