<?php

namespace Abc\Job\Symfony\Command;

use Abc\Job\Broker\RouteCollection;
use Abc\Job\Client\RouteClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterRoutesCommand extends Command
{

    protected static $defaultName = 'abc:register:routes';

    /**
     * @var RouteClient
     */
    private $routeClient;

    /**
     * @var RouteCollection
     */
    private $routeCollection;

    public function __construct(RouteClient $routeClient, RouteCollection $routeCollection)
    {
        $this->routeClient = $routeClient;
        $this->routeCollection = $routeCollection;

        parent::__construct(static::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Registers job routes on the job server')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $routes = $this->routeCollection->all();

        if(0 < count($routes))
        {
            $this->routeClient->add($routes);

            $output->writeln('Registered routes');
        }

        return 0;
    }
}
