<?php

namespace Abc\Job\Broker;

/**
 * A Route provides information about where a job is delivered and where the job sends his replies.
 */
class Route
{
    /**
     * @var string
     */
    private $jobName;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @var string
     */
    private $replyTo;

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

    public function getReplyTo(): string
    {
        return $this->replyTo;
    }

    public static function fromArray(array $route): Route
    {
        list('name' => $jobName, 'queue' => $queue, 'reply' => $replyTo) = $route;

        return new self($jobName, $queue, $replyTo);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->jobName,
            'queue' => $this->queueName,
            'reply' => $this->replyTo,
        ];
    }
}
