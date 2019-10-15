<?php

namespace Abc\Job\Broker;

class RouteCollection
{
    /**
     * @var Route[]
     */
    private $routes = [];

    /**
     * @param Route[] $routes
     */
    public function __construct(array $routes = [])
    {
        foreach ($routes as $route) {
            $this->routes[$route->getName()] = $route;
        }
    }

    public function add(Route $route): void
    {
        $this->routes[$route->getName()] = $route;
    }

    /**
     * @return Route[]
     */
    public function all(): array
    {
        return array_values($this->routes);
    }

    public function get(string $jobName): ?Route
    {
        return isset($this->routes[$jobName]) ? $this->routes[$jobName] : null;
    }

    public function toArray(): array
    {
        $rawRoutes = [];
        foreach ($this->all() as $route) {
            $rawRoutes[] = $route->toArray();
        }

        return $rawRoutes;
    }

    public static function fromArray(array $rawRoutes): self
    {
        $routes = [];
        foreach ($rawRoutes as $rawRoute) {
            $routes[] = Route::fromArray($rawRoute);
        }

        return new self($routes);
    }
}
