<?php

namespace Abc\Job\Processor;

class Result
{
    /**
     * @var string
     */
    private $output;

    /**
     * @var string
     */
    private $status;

    /**
     * @see ProcessorInterface::COMPLETE for more details
     */
    const COMPLETE = ProcessorInterface::COMPLETE;

    /**
     * @see ProcessorInterface::FAILED for more details
     */
    const FAILED = ProcessorInterface::FAILED;

    public function __construct(string $status, ?string $output = null)
    {
        $this->status = $status;
        $this->output = $output;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }
}
