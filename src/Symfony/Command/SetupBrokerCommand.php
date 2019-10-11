<?php

namespace Abc\Job\Symfony\Command;

use Abc\Job\Interop\DriverInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class SetupBrokerCommand extends Command
{
    protected static $defaultName = 'abc:setup-broker';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var DriverInterface
     */
    private $driver;

    public function __construct(ContainerInterface $container, DriverInterface $driver)
    {
        $this->container = $container;
        $this->driver = $driver;

        parent::__construct(static::$defaultName);
    }

    protected function configure(): void
    {
        $this->setAliases(['abc:sb'])->setDescription('Setup broker. Configure the broker, creates queues and so on.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->driver->setupBroker(new ConsoleLogger($output));

        $output->writeln('Broker set up');

        return null;
    }
}

