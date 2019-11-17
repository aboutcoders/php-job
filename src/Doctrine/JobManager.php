<?php

namespace Abc\Job\Doctrine;

use Abc\Job\JobFilter;
use Abc\Job\Model\JobInterface;
use Abc\Job\Model\JobManager as BaseJobManager;
use Doctrine\Common\Persistence\ObjectManager;

class JobManager extends BaseJobManager
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

    public function __construct(ObjectManager $om, string $class)
    {
        $this->objectManager = $om;
        $this->repository = $om->getRepository($class);

        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function save(JobInterface $job, bool $andFlush = true): void
    {
        $this->setDates($job);
        $this->objectManager->persist($job);

        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    public function delete(JobInterface $job, bool $andFlush = true): void
    {
        $this->objectManager->remove($job);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    public function refresh(JobInterface $job): void
    {
        $this->objectManager->refresh($job);
    }

    public function find(string $id): ?JobInterface
    {
        return $this->repository->find($id);
    }

    public function findBy(JobFilter $filter = null): array
    {
        $criteria = [];

        if (! empty($filter->getIds())) {
            $criteria['id'] = $filter->getIds();
        }

        if (! empty($filter->getNames())) {
            $criteria['name'] = $filter->getNames();
        }

        if (! empty($filter->getExternalIds())) {
            $criteria['externalId'] = $filter->getExternalIds();
        }

        if (! empty($filter->getStatus())) {
            $criteria['status'] = $filter->getStatus();
        }

        return $this->repository->findBy($criteria, ['createdAt' => 'DESC']);
    }

    private function setDates(JobInterface $job): void
    {
        if (null == $job->getCreatedAt()) {
            $job->setCreatedAt(new \DateTime(null, new \DateTimeZone('UTC')));
        }

        $job->setUpdatedAt(new \DateTime(null, new \DateTimeZone('UTC')));
        foreach ($job->getChildren() as $child) {
            $this->setDates($child);
        }
    }
}
