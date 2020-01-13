<?php

namespace Abc\Job\Doctrine;

use Abc\Job\JobFilter;
use Abc\Job\Model\JobInterface;
use Abc\Job\Model\JobManager as BaseJobManager;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\Persistence\ObjectRepository;

class JobManager extends BaseJobManager
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var ObjectRepository
     */
    protected $repository;

    public function __construct(EntityManager $em, string $class)
    {
        $this->entityManager = $em;
        $this->repository = $em->getRepository($class);

        $metadata = $em->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function save(JobInterface $job, bool $andFlush = true): void
    {
        $this->setDates($job);
        $this->entityManager->persist($job);

        if ($andFlush) {
            $this->entityManager->flush();
        }
    }

    public function delete(JobInterface $job, bool $andFlush = true): void
    {
        $this->entityManager->remove($job);
        if ($andFlush) {
            $this->entityManager->flush();
        }
    }

    public function refresh(JobInterface $job): void
    {
        $this->entityManager->refresh($job);
    }

    public function find(string $id): ?JobInterface
    {
        return $this->repository->find($id);
    }

    public function findBy(JobFilter $filter = null): array
    {
        $criteria = [];

        if (! empty($filter->getIds())) {
            $criteria['id'] = array_merge($filter->getIds(), $ids);
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

        if ($filter->isLatest()) {
            return $this->queryIdsByDistinctExternalId($filter->getExternalIds());
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

    private function queryIdsByDistinctExternalId(array $externalIds): array
    {
        $jobs = [];
        foreach ($externalIds as $externalId) {
            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('j');
            $qb->from($this->class, 'j');
            $qb->where($qb->expr()->eq('j.externalId', '?1'));
            $qb->orderBy('j.createdAt', 'DESC');
            $qb->setParameter(1, $externalId);

            $query = $qb->getQuery();
            $query->setMaxResults(1);
            $jobs[] = $query->getSingleResult($query::HYDRATE_OBJECT);
        }

        return $jobs;
    }
}
