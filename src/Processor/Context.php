<?php

namespace Abc\Job\Processor;

class Context
{
    /**
     * @var string
     */
    private $jobId;

    /**
     * @var \Closure
     */
    private $sendOutputCallback;

    public function __construct(string $jobId, \Closure $sendOutputCallback)
    {
        $this->jobId = $jobId;
        $this->sendOutputCallback = $sendOutputCallback;
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function sendOutput(string $output): void
    {
        ($this->sendOutputCallback)($output);
    }
}
