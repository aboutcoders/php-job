<?php

namespace Abc\Job\Symfony\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessQueueCommand extends BaseProcessCommand
{
    protected static $defaultName = 'abc:process:queue';

    protected function configure(): void
    {
        $this->setDescription('A worker that processes jobs from a broker'.'To use this worker you have to explicitly set the queues to consume from');

        $queuesArgument = $this->consumeCommand->getDefinition()->getArgument('queues');

        $this->addArgument($queuesArgument->getName(), InputArgument::REQUIRED | InputArgument::IS_ARRAY, $queuesArgument->getDescription(), $queuesArgument->getDefault());

        $this->configureOptions();
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $parameters = [
            'processor' => 'job',
            'queues' => ! is_array($input->getArgument('queues')) ? [$input->getArgument('queues')] : $input->getArgument('queues'),
        ];

        $parameters = $this->buildParameters($input, $parameters);

        return $this->consumeCommand->run(new ArrayInput($parameters), $output);
    }
}
