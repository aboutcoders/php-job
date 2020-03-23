<?php

namespace Abc\Job\Client;

use Abc\ApiProblem\ApiProblemException;
use Abc\Job\Broker\BrokerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

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

    public function getRoutes(): ?array
    {
        //fixme: not implemented yet (not needed yet)
        return null;
    }

    /**
     * @inheritDoc
     * @throws ApiProblemException
     */
    public function setUp(LoggerInterface $logger = null): void
    {
        $logger = $logger ?: new NullLogger();

        try {
            $this->client->setup($this->name);

            $logger->info(sprintf('Successfully setup broker "%s"', $this->name));
        } catch (ApiProblemException $exception) {
            $logger->error(
                sprintf(
                    'Broker setup failed: %s %s: %s',
                    $exception->getCode(),
                    $exception->getApiProblem()->getTitle(),
                    $exception->getApiProblem()->getDetail()
                )
            );

            throw $exception;
        }
    }
}
