<?php

namespace Abc\Job\Client;

use Abc\ApiProblem\ApiProblemException;
use Abc\Job\Broker\BrokerInterface;
use Psr\Log\LoggerInterface;

class BoundBrokerClient implements BrokerInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var BrokerClient
     */
    private $client;

    public function __construct(string $name, BrokerClient $client)
    {
        $this->name = $name;
        $this->client = $client;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setUp(LoggerInterface $logger = null): void
    {
        try {
            $this->client->setup($this->name);

            $logger->info(sprintf('Successfully setup broker "%s"', $this->name));
        } catch (ApiProblemException $exception) {
            $logger->error(
                sprintf(
                    'Broker setup failed with error: %s code: %s',
                    $exception->getApiProblem()->getDetail(),
                    $exception->getCode()
                )
            );
        }
    }
}