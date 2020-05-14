<?php

namespace Abc\Job\Doctrine;

use Abc\Job\CronJobFilter;
use Abc\Job\Model\CronJobInterface;
use Abc\Job\OrderBy;
use Abc\Job\Model\CronJobManager as BaseCronJobManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

class CronJobManager extends BaseCronJobManager
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
     * @var EntityRepository
     */
    protected $repository;

    public function __construct(EntityManager $em, string $class)
    {
        $this->entityManager = $em;
        $this->class = $class;
        $this->repository = $em->getRepository($class);

        parent::__construct($class);
    }

    public function save(CronJobInterface $cronJob, bool $andFlush = true): void
    {
        $cronJob->setJobJson($cronJob->getJob()->toJson());
        $cronJob->setName($cronJob->getJob()->getName());
        $cronJob->setExternalId($cronJob->getJob()->getExternalId());

        $this->setDates($cronJob);
        $this->entityManager->persist($cronJob);

        if ($andFlush) {
            $this->entityManager->flush();
        }
    }

    public function delete(CronJobInterface $schedule, bool $andFlush = true): void
    {
        $this->entityManager->remove($schedule);
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
        $query = $qb->delete()->from($this->getClass(), 'cj')->getQuery();

        return $query->getSingleScalarResult();
    }

    public function refresh(CronJobInterface $job): void
    {
        $this->entityManager->refresh($job);
    }

    public function find(string $id): ?CronJobInterface
    {
        return $this->repository->find($id);
    }

    public function findBy(
        CronJobFilter $filter = null,
        OrderBy $orderBy = null,
        int $limit = null,
        int $offset = null
    ): array {
        $qb = $this->createQueryBuilder();

        if (null !== $filter) {

            if (!empty($filter->getIds())) {
                $qb->andWhere($qb->expr()->in('cj.id', '?1'));
                $qb->setParameter(1, $filter->getIds());
            }

            if (!empty($filter->getNames())) {
                $qb->andWhere($qb->expr()->in('cj.name', '?2'));
                $qb->setParameter(2, $filter->getNames());
            }

            if (!empty($filter->getExternalIds())) {
                $qb->andWhere($qb->expr()->in('cj.externalId', '?4'));
                $qb->setParameter(4, $filter->getExternalIds());
            }
        }

        $query = $qb->getQuery();
        $query->setFirstResult(null === $filter ? null : $filter->getOffset());
        $query->setMaxResults(null === $filter ? null : $filter->getLimit());

        return $query->getResult();
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('cj');
        $qb->from($this->class, 'cj');
        $qb->orderBy('cj.createdAt', 'DESC');

        return $qb;
    }

    private function setDates(CronJobInterface $schedule): void
    {
        if (null == $schedule->getCreatedAt()) {
            $schedule->setCreatedAt(new \DateTime(null, new \DateTimeZone('UTC')));
        }

        $schedule->setUpdatedAt(new \DateTime(null, new \DateTimeZone('UTC')));
    }
}
