<?php

namespace Abc\Job\Client;

use Abc\ApiProblem\ApiProblemException;

class BrokerClient extends AbstractClient
{
    /**
     * @var BrokerHttpClient
     */
    private $client;

    public function __construct(BrokerHttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * @throws ApiProblemException
     */
    public function setup(string $name): void
    {
        $response = $this->client->setup($name);

        $this->validateResponse($response);
    }
}