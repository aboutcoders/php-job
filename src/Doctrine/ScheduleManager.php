<?php

namespace Abc\Job\Doctrine;

use Abc\Job\Model\ScheduleInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Abc\Job\Model\ScheduleManager as BaseScheduleManager;

class ScheduleManager extends BaseScheduleManager
{
    /**
     * @var string
     */
    protected $class;

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

        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function save(ScheduleInterface $schedule, bool $andFlush = true): void
    {
        $this->setDates($schedule);
        $this->objectManager->persist($schedule);

        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    public function delete(ScheduleInterface $schedule, bool $andFlush = true): void
    {
        $this->objectManager->remove($schedule);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    public function refresh(ScheduleInterface $job): void
    {
        $this->objectManager->refresh($job);
    }

    public function find(string $id): ?ScheduleInterface
    {
        return $this->repository->find($id);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    private function setDates(ScheduleInterface $schedule): void
    {
        if (null == $schedule->getCreatedAt()) {
            $schedule->setCreatedAt(new \DateTime(null, new \DateTimeZone('UTC')));
        }

        $schedule->setUpdatedAt(new \DateTime(null, new \DateTimeZone('UTC')));
    }
}
