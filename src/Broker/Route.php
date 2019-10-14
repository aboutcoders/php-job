<?php

namespace Abc\Job\Broker;

/**
 * A Route provides information about where to send a job where a job sends it's replies.
 */
class Route
{
    /**
     * @var string
     */
    protected $jobName;

    /**
     * @var string
     */
    protected $queueName;

    /**
     * @var string
     */
    protected $replyTo;

    public function __construct(string $jobName, string $queueName, string $replyTo)
    {
        $this->jobName = $jobName;
        $this->queueName = $queueName;
        $this->replyTo = $replyTo;
    }

    public function getJobName(): string
    {
        return $this->jobName;
    }

    public function getQueueName(): string
    {
        return $this->queueName;
    }

    public function setQueueName(string $queueName): void
    {
        $this->queueName = $queueName;
    }

    public function getReplyTo(): string
    {
        return $this->replyTo;
    }

    public function setReplyTo(string $replyTo): void
    {
        $this->replyTo = $replyTo;
    }

    public static function fromArray(array $rawRoute): Route
    {
        return new static($rawRoute['jobName'], $rawRoute['queueName'] ?? null, $rawRoute['replyTo'] ?? null);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function toJson()
    {
        return json_encode((object) $this->toArray());
    }
}
