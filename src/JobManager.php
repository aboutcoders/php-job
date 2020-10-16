<?php

namespace Abc\Job;

use Abc\Job\Model\JobInterface;
use Abc\Job\Model\JobManagerInterface;
use Abc\Job\PreSaveExtension\ChainExtension;
use Abc\Job\PreSaveExtension\PreSaveExtensionInterface;
use Abc\Job\Util\DateUtil;

class JobManager
{
    /**
     * @var JobManagerInterface
     */
    private $manager;

    /**
     * @var PreSaveExtensionInterface
     */
    private $extension;

    public function __construct(JobManagerInterface $manager, PreSaveExtensionInterface $extension = null)
    {
        $this->manager = $manager;
        $this->extension = $extension ?: new ChainExtension([]);
    }

    public function create(Job $job): JobInterface
    {
        return static::fromArray($this->manager->create(), $job->toArray());
    }

    public function delete(JobInterface $job): void
    {
        $this->manager->delete($job);
    }

    public function deleteAll(): int
    {
        return $this->manager->deleteAll();
    }

    public function refresh(JobInterface $job): void
    {
        $this->manager->refresh($job);
    }

    public function save(JobInterface $job): void
    {
        $this->extension->onPreSave($job);

        $this->manager->save($job);
    }

    public function find(string $id): ?JobInterface
    {
        return $this->manager->find($id);
    }

    public function findBy(JobFilter $filter = null): array
    {
        return $this->manager->findBy($filter);
    }

    public function existsConcurrent(Job $job): bool
    {
        return $this->manager->existsConcurrent($job);
    }

    public static function findNext(array $children, bool $sorted = false): ?JobInterface
    {
        $children = $sorted ? $children : static::sortByPosition($children);
        for ($i = 0; $i < count($children); $i++) {
            if (Status::WAITING == $children[$i]->getStatus()) {
                return $children[$i];
            }
        }

        return null;
    }

    /**≤
     * @param  JobInterface[]  $children
     * @return JobInterface[]
     */
    public static function sortByPosition(array $children): array
    {
        uasort(
            $children,
            function (JobInterface $left, JobInterface $right) {
                return ($left->getPosition() < $right->getPosition()) ? -1 : 1;
            }
        );

        return $children;
    }

    public static function fromArray(JobInterface $job, array $data): JobInterface
    {
        $job->setType(new Type($data['type']) ?? null);
        $job->setId($data['id'] ?? null);
        $job->setName($data['name'] ?? null);
        $job->setStatus($data['status'] ?? $job->getStatus());
        $job->setInput($data['input'] ?? null);
        $job->setOutput($data['output'] ?? null);
        $job->setAllowFailure($data['allowFailure'] ?? false);
        $job->setProcessingTime($data['processingTime'] ?? 0);
        $job->setRestarts($data['restarts'] ?? 0);
        $job->setExternalId($data['externalId'] ?? null);
        $job->setCreatedAt(!isset($data['created']) ? null : DateUtil::createDate(strtotime($data['created'])));
        $job->setCompletedAt(!isset($data['completed']) ? null : DateUtil::createDate(strtotime($data['completed'])));
        $job->setUpdatedAt(!isset($data['updated']) ? null : DateUtil::createDate(strtotime($data['updated'])));

        if (isset($data['children'])) {
            foreach ($data['children'] as $childArray) {
                $job->addChild(self::fromArray($job::create(), $childArray));
            }
        }

        return $job;
    }
}
