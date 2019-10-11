<?php

namespace Abc\Job;

use Abc\ApiProblem\ApiProblemException;
use Abc\Job\Broker\Route;
use GuzzleHttp\Exception\RequestException;

class RouteClient extends BaseClient
{
    /**
     * @var HttpRouteClient
     */
    private $client;

    public function __construct(HttpRouteClient $client)
    {
        $this->client = $client;
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

        try {
            $this->client->add($json, ['http_errors' => true]);
        } catch (RequestException $exception) {
            $this->throwApiProblemException($exception);
        }
    }

    /**
     * @return Route[]
     * @throws ApiProblemException
     */
    public function all(): array
    {
        try {
            $response = $this->client->all(['http_errors' => true]);
        } catch (RequestException $exception) {
            $this->throwApiProblemException($exception);
        }

        $rawRoutes = json_decode($response->getBody()->getContents(), true);

        $routes = [];
        foreach ($rawRoutes as $rawRoute) {
            $routes[] = Route::fromArray($rawRoute);
        }

        return $routes;
    }
}
