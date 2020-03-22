<?php

namespace Abc\Job\Symfony\Command;

use Abc\Job\Broker\RegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class SetupBrokerCommand extends Command
{
    protected static $defaultName = 'abc:broker:setup';

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct(static::$defaultName);

        $this->registry = $registry;
    }

    /**
     * @var RegistryInterface
     */
    private $registry;

    protected function configure(): void
    {
        $this->setDescription('Setup a broker');
        $this->addArgument('name', InputArgument::OPTIONAL, 'The broker to setup', 'default');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        if (!$this->registry->exists($name)) {
            throw new \LogicException(sprintf('Broker "%s" is not supported', $name));
        }

        $this->registry->get($name)->setup(new ConsoleLogger($output));

        return 0;
    }
}