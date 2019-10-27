<?php

namespace Abc\Job\Processor;

class ProcessorRegistry
{
    /**
     * @var ProcessorInterface[]
     */
    private $processors = [];

    public function add(string $jobName, ProcessorInterface $processor): void
    {
        if ($this->exists($jobName) && $processor !== $this->processors[$jobName]) {
            throw new \InvalidArgumentException(sprintf('Processor %s exists with the same job name "%s"', get_class($this->processors[$jobName]), $jobName));
        }

        $this->processors[$jobName] = $processor;
    }

    public function get(string $jobName): ?ProcessorInterface
    {
        return $this->exists($jobName) ? $this->processors[$jobName] : $this->fallback;
    }

    public function exists(string $jobName): bool
    {
        return isset($this->processors[$jobName]);
    }
}
