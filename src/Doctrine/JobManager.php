<?php

namespace Abc\Job\Doctrine;

use Abc\Job\JobFilter;
use Abc\Job\Model\JobInterface;
use Abc\Job\Model\JobManager as BaseJobManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * @return int The number of deleted jobs
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function deleteAll(): int
    {
        $qb = $this->entityManager->createQueryBuilder();

        // delete children
        $query = $qb->delete()
            ->from($this->class, 'j')
            ->where($qb->expr()->isNotNull('j.parent'))
            ->getQuery()
        ;

        $query->getSingleScalarResult();

        // delete parents
        $qb = $this->entityManager->createQueryBuilder();

        // delete parents
        $query = $qb->delete()
            ->from($this->class, 'j')->getQuery();

        return $query->getSingleScalarResult();
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
        $qb = $this->createQueryBuilder();

        if (null !== $filter) {
            if ($filter->isLatest()) {
                return $this->findByLatest($filter);
            }

            if (! empty($filter->getIds())) {
                $qb->andWhere($qb->expr()->in('j.id', '?1'));
                $qb->setParameter(1, $filter->getIds());
            }

            if (! empty($filter->getNames())) {
                $qb->andWhere($qb->expr()->in('j.name', '?2'));
                $qb->setParameter(2, $filter->getNames());
            }

            if (! empty($filter->getStatus())) {
                $qb->andWhere($qb->expr()->in('j.status', '?3'));
                $qb->setParameter(3, $filter->getStatus());
            }

            if (! empty($filter->getExternalIds())) {
                $qb->andWhere($qb->expr()->in('j.externalId', '?4'));
                $qb->setParameter(4, $filter->getExternalIds());
            }
        }

        $query = $qb->getQuery();
        $query->setFirstResult(null === $filter ? null : $filter->getOffset());
        $query->setMaxResults(null === $filter ? null : $filter->getLimit());

        return $query->getResult();
    }

    private function findByLatest(JobFilter $filter): array
    {
        $jobs = [];

        foreach ($filter->getExternalIds() as $externalId) {

            $qb = $this->createQueryBuilder();
            $qb->where($qb->expr()->eq('j.externalId', '?1'));
            $qb->setParameter(1, $externalId);

            $query = $qb->getQuery();
            $query->setMaxResults(1);
            $jobs[] = $query->getSingleResult($query::HYDRATE_OBJECT);
        }

        return $jobs;
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('j');
        $qb->from($this->class, 'j');
        $qb->where($qb->expr()->isNull('j.parent'));
        $qb->orderBy('j.createdAt', 'DESC');

        return $qb;
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
