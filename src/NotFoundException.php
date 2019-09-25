<?php

namespace Abc\Job;

class NotFoundException extends \InvalidArgumentException
{
    /**
     * @var string
     */
    private $jobId;

    public function __construct(string $jobId)
    {
        $this->jobId = $jobId;
        parent::__construct(sprintf('Job with id "%s" not found', $jobId), 404);
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }
}
