<?php

namespace Abc\Job\Broker;

class Config
{
    const NAME = 'abc.job.name';

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $separator;

    /**
     * @var string
     */
    private $defaultQueue;

    /**
     * @var string
     */
    private $defaultReplyTo;

    function __construct(string $prefix, string $separator, string $defaultQueue, string $defaultReplyTo)
    {
        $this->prefix = $prefix;
        $this->separator = $separator;
        $this->defaultQueue = $defaultQueue;
        $this->defaultReplyTo = $defaultReplyTo;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function getDefaultQueue(): string
    {
        return $this->defaultQueue;
    }

    public function getDefaultReplyTo(): string
    {
        return $this->defaultReplyTo;
    }
}
