<?php

namespace Abc\Job;

class NoRouteException extends \RuntimeException
{
    /**
     * @var string
     */
    private $jobName;

    public function __construct(string $jobName)
    {
        $this->jobName = $jobName;

        parent::__construct(sprintf('No route found for job %s', $jobName));
    }

    /**
     * @return string
     */
    public function getJobName(): string
    {
        return $this->jobName;
    }
}
