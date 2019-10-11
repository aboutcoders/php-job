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
        $oldRoute = $this->routeManager->find($route->getJobName());
        if (null == $oldRoute) {
            $this->routeManager->save($route);

            $this->logger->info(sprintf('[RouteRegistry] Registered route for job %s with queueName %s and replyTo %s', $route->getJobName(), $route->getQueueName(), $route->getReplyTo()));

            return;
        }

        $message = null;
        if (null !== $route->getQueueName() && $route->getQueueName() != $oldRoute->getQueueName()) {
            $message = sprintf('Changed route for job %s, set queueName from %s to %s', $route->getJobName(), $oldRoute->getQueueName(), $route->getQueueName());
            $oldRoute->setQueueName($route->getQueueName());
        }

        if (null !== $route->getReplyTo() && $route->getReplyTo() != $oldRoute->getReplyTo()) {
            $message = sprintf('Changed route for job %s, set replyTo from %s to %s', $route->getJobName(), $oldRoute->getReplyTo(), $route->getReplyTo());
            $oldRoute->setReplyTo($route->getReplyTo());
        }

        if (null != $message) {
            $this->routeManager->save($oldRoute);
            $this->logger->notice('[RouteRegistry] '.$message);
        }
    }
}
