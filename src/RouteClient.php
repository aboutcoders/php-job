<?php

namespace Abc\Job;

use Abc\ApiProblem\ApiProblemException;
use Abc\Job\Broker\Route;
use Psr\Log\LoggerInterface;

class RouteClient extends BaseClient
{
    /**
     * @var HttpRouteClient
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(HttpRouteClient $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @param Route[] $routes
     * @throws ApiProblemException
     */
    public function add(array $routes): void
    {
        $rawRoutes = [];
        foreach ($routes as $route) {
            $rawRoutes[] = (object) $route->toArray();
        }

        $json = json_encode($rawRoutes);

        $response = $this->client->add($json);

        $this->validateResponse($response);

        $this->logger->info(sprintf('[RouteClient] Added routes %s', $json));
    }

    /**
     * @return Route[]
     * @throws ApiProblemException
     */
    public function all(): array
    {
        $response = $this->client->all();

        $this->validateResponse($response);

        $rawRoutes = json_decode($response->getBody()->getContents(), true);

        $routes = [];
        foreach ($rawRoutes as $rawRoute) {
            $routes[] = Route::fromArray($rawRoute);
        }

        return $routes;
    }
}
