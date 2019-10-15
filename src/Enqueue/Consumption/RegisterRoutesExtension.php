<?php

namespace Abc\Job\Enqueue\Consumption;

use Abc\Job\Broker\RouteCollection;
use Abc\Job\RouteClient;
use Enqueue\Consumption\Context\Start;
use Enqueue\Consumption\StartExtensionInterface;

class RegisterRoutesExtension implements StartExtensionInterface
{
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
    }

    /**
     * @param Start $context
     * @throws \Abc\ApiProblem\ApiProblemException
     */
    public function onStart(Start $context): void
    {
        $this->routeClient->add($this->routeCollection->all());
    }
}
