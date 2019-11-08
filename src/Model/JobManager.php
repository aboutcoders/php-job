<?php

namespace Abc\Job\Model;

use Abc\Job\Job;
use Abc\Job\Status;
use Abc\Job\Type;
use LogicException;

abstract class JobManager implements JobManagerInterface
{
    abstract protected function getClass(): string;

    public function create(Job $job): JobInterface
    {
        $class = $this->getClass();
        if (! is_subclass_of($class, JobInterface::class)) {
            throw new LogicException(sprintf('Class %s must implement %s', $class, JobInterface::class));
        }

        /** @var JobInterface $class */
        return $this->fromArray($class::create(), $job->toArray());
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
     * @param JobInterface[] $children
     * @return JobInterface[]
     */
    public static function sortByPosition(array $children): array
    {
        uasort($children, function (JobInterface $left, JobInterface $right) {
            return ($left->getPosition() < $right->getPosition()) ? -1 : 1;
        });

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
        $job->setExternalId($data['externalId'] ?? null);
        $job->setCreatedAt(! isset($data['created']) ? null : static::createDate(strtotime($data['created'])));
        $job->setCompletedAt(! isset($data['completed']) ? null : static::createDate(strtotime($data['completed'])));
        $job->setUpdatedAt(! isset($data['updated']) ? null : static::createDate(strtotime($data['updated'])));

        if (isset($data['children'])) {
            foreach ($data['children'] as $childArray) {
                $job->addChild(self::fromArray($job::create(), $childArray));
            }
        }

        return $job;
    }

    protected static function createDate(int $timestamp): \DateTime
    {
        return new \DateTime('@'.$timestamp, new \DateTimeZone('UTC'));
    }
}
