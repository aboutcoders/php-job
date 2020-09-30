<?php

namespace Abc\Job;

use Abc\Job\Model\CronJobManagerInterface;
use Abc\Scheduler\ConcurrencyPolicy;

class CronJobManager
{
    /**
     * @var CronJobManagerInterface
     */
    private $entityManager;

    public function __construct(CronJobManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param  CronJobFilter|null  $filter
     * @param  OrderBy|null  $orderBy
     * @param  int|null  $limit
     * @param  int|null  $offset
     * @return CronJob[]
     */
    public function list(
        CronJobFilter $filter = null,
        OrderBy $orderBy = null,
        int $limit = null,
        int $offset = null
    ): array {
        return $this->entityManager->findBy($filter, $orderBy, $limit, $offset);
    }

    public function find(string $id): ?CronJob
    {
        return $this->entityManager->find($id);
    }

    public function create(string $schedule, Job $job, ConcurrencyPolicy $policy = null): CronJob
    {
        $cronJob = $this->entityManager->create($schedule, $job);

        if (null !== $policy) {
            $cronJob->setConcurrencyPolicy($policy);
        }

        $this->entityManager->save($cronJob);

        return $cronJob;
    }

    public function update(CronJob $cronJob): void
    {
        $this->validateInstance($cronJob);
        $this->entityManager->save($cronJob);
    }

    public function delete(CronJob $cronJob): void
    {
        $this->validateInstance($cronJob);
        $this->entityManager->delete($cronJob);
    }

    public function deleteAll(): int
    {
        return $this->entityManager->deleteAll();
    }

    private function validateInstance(CronJob $cronJob)
    {
        $class = $this->entityManager->getClass();
        if (!$cronJob instanceof $class) {
            throw new \InvalidArgumentException(sprintf('%s is not an instance of %s', get_class($cronJob), $class));
        }
    }
}
