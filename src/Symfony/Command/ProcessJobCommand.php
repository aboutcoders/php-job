<?php

namespace Abc\Job\Symfony\Command;

use Abc\Job\Broker\RouteCollection;
use Abc\Job\Client\RouteClient;
use Abc\Job\Interop\JobConsumer;
use Abc\Job\Processor\ProcessorRegistry;
use Enqueue\Symfony\Consumption\ConfigurableConsumeCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessJobCommand extends BaseProcessCommand
{
    protected static $defaultName = 'abc:process:job';

    /**
     * @var ProcessorRegistry
     */
    private $processorRegistry;

    /**
     * @var RouteClient
     */
    private $routeClient;

    /**
     * @var JobConsumer
     */
    private $jobConsumer;

    public function __construct(
        ProcessorRegistry $processorRegistry,
        RouteClient $routeClient,
        JobConsumer $jobConsumer,
        ConfigurableConsumeCommand $consumeCommand
    ) {
        $this->processorRegistry = $processorRegistry;
        $this->routeClient = $routeClient;
        $this->jobConsumer = $jobConsumer;

        parent::__construct($consumeCommand);
    }

    protected function configure(): void
    {
        $this->setDescription('A worker that processes jobs from a broker. '.'To use this worker you have to explicitly set the jobs to process');

        $this->addArgument('jobs', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'A job to process');

        $this->configureConsumeCommandOptions();
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $jobs = is_array($input->getArgument('jobs')) ? $input->getArgument('jobs') : [$input->getArgument('jobs')];

        $queues = $this->getQueues($jobs);

        $parameters = $this->buildParameters($input, [
            'processor' => 'job',
            'queues' => $queues,
        ]);

        $this->jobConsumer->setJobs($jobs);

        return $this->consumeCommand->run(new ArrayInput($parameters), $output);
    }

    private function getQueues(?array $jobs): array
    {
        $routeCollection = new RouteCollection($this->routeClient->all());
        $queues = [];

        foreach ($jobs as $jobName) {

            if (! $this->processorRegistry->exists($jobName)) {
                throw new \InvalidArgumentException(sprintf('There is no processor registered for job "%s"', $jobName));
            }

            $route = $routeCollection->get($jobName);
            if (null == $route) {
                throw new \InvalidArgumentException(sprintf('The is no route registered for job "%s"', $jobName));
            }

            $queues[] = $route->getQueue();
        }

        return array_unique($queues);
    }
}
