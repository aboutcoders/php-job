<?php

namespace Abc\Job\Broker;

use Abc\Job\Model\RouteManagerInterface;
use Psr\Log\LoggerInterface;

class ManagedRouteRegistry implements RouteRegistryInterface
{
    /**
     * @var RouteManagerInterface
     */
    private $routeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(RouteManagerInterface $routeManager, LoggerInterface $logger)
    {
        $this->routeManager = $routeManager;
        $this->logger = $logger;
    }

    /**
     * @return Route[]
     */
    public function all(): array
    {
        return $this->routeManager->all();
    }

    public function get(string $jobName): ?Route
    {
        return $this->routeManager->find($jobName);
    }

    public function add(Route $route): void
    {
        // fixme: not transaction save (multiple add operations at one time)
        $oldRoute = $this->routeManager->find($route->getName());
        if (null == $oldRoute) {
            $this->routeManager->save($route);

            $this->logger->info(sprintf('[RouteRegistry] Registered route for job %s with queue %s and replyTo %s', $route->getName(), $route->getQueue(), $route->getReplyTo()));

            return;
        }

        $message = null;
        if (null !== $route->getQueue() && $route->getQueue() != $oldRoute->getQueue()) {
            $message = sprintf('Changed route for job %s, set queue from %s to %s', $route->getName(), $oldRoute->getQueue(), $route->getQueue());
            $oldRoute->setQueue($route->getQueue());
        }

        if (null !== $route->getReplyTo() && $route->getReplyTo() != $oldRoute->getReplyTo()) {
            $message = sprintf('Changed route for job %s, set replyTo from %s to %s', $route->getName(), $oldRoute->getReplyTo(), $route->getReplyTo());
            $oldRoute->setReplyTo($route->getReplyTo());
        }

        if (null != $message) {
            $this->routeManager->save($oldRoute);
            $this->logger->notice('[RouteRegistry] '.$message);
        }
    }
}
