<?php

namespace Abc\Job\Model;

abstract class CronJobManager implements CronJobManagerInterface
{
    /**
     * @var string
     */
    private $class;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function create(string $scheduleExpression, \Abc\Job\Job $job): CronJobInterface
    {
        /** @var CronJobInterface $cls */
        $cls = $this->getClass();

        return $cls::create($scheduleExpression, $job);
    }

    public function getClass(): string
    {
        return $this->class;
    }
}
