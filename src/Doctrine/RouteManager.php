<?php

namespace Abc\Job\Doctrine;

use Abc\Job\Broker\Route;
use Abc\Job\Model\RouteManagerInterface;
use Doctrine\Common\Persistence\ObjectManager;

class RouteManager implements RouteManagerInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var
     */
    protected $repository;

    public function __construct(ObjectManager $om)
    {
        $this->class = \Abc\Job\Model\Route::class;
        $this->objectManager = $om;
        $this->repository = $om->getRepository($this->class);

        $metadata = $om->getClassMetadata($this->class);
        $this->class = $metadata->getName();
    }

    public function all(): array
    {
        return $this->repository->findAll();
    }

    public function find(string $jobName): ?Route
    {
        /** @var \Abc\Job\Model\Route $route */
        $route = $this->repository->find($jobName);

        return $route;
    }

    public function save(Route $route, bool $andFlush = true): void
    {
        if (! $route instanceof \Abc\Job\Model\Route) {
            $route = \Abc\Job\Model\Route::fromArray($route->toArray());
        }

        $this->objectManager->persist($route);

        if ($andFlush) {
            $this->objectManager->flush();
        }
    }
}
