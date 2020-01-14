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

class CronJobManager extends BaseCronJobManager
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EntityRepository
     */
    protected $repository;

    public function __construct(EntityManager $em, string $class)
    {
        $this->entityManager = $em;

        $this->repository = $em->getRepository($class);

        parent::__construct($class);
    }

    public function save(CronJobInterface $cronJob, bool $andFlush = true): void
    {
        $cronJob->setJobJson($cronJob->getJob()->toJson());
        $cronJob->setName($cronJob->getJob()->getName());

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
        return $this->repository->findBy([], [], $limit, $offset);
    }

    private function setDates(CronJobInterface $schedule): void
    {
        if (null == $schedule->getCreatedAt()) {
            $schedule->setCreatedAt(new \DateTime(null, new \DateTimeZone('UTC')));
        }

        $schedule->setUpdatedAt(new \DateTime(null, new \DateTimeZone('UTC')));
    }
}
