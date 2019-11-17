<?php

namespace Abc\Job\Doctrine;

use Abc\Job\CronJobFilter;
use Abc\Job\Model\CronJobInterface;
use Abc\Job\OrderBy;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Abc\Job\Model\CronJobManager as BaseCronJobManager;

class CronJobManager extends BaseCronJobManager
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ObjectRepository
     */
    protected $repository;

    public function __construct(ObjectManager $om, string $class)
    {
        $this->objectManager = $om;
        $this->repository = $om->getRepository($class);

        parent::__construct($class);
    }

    public function save(CronJobInterface $cronJob, bool $andFlush = true): void
    {
        $cronJob->setJobJson($cronJob->getJob()->toJson());
        $cronJob->setName($cronJob->getJob()->getName());

        $this->setDates($cronJob);
        $this->objectManager->persist($cronJob);

        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    public function delete(CronJobInterface $schedule, bool $andFlush = true): void
    {
        $this->objectManager->remove($schedule);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    public function refresh(CronJobInterface $job): void
    {
        $this->objectManager->refresh($job);
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
