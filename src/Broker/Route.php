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
    protected $name;

    /**
     * @var string
     */
    protected $queue;

    /**
     * @var string
     */
    protected $replyTo;

    public function __construct(string $name, string $queue, string $replyTo)
    {
        $this->name = $name;
        $this->queue = $queue;
        $this->replyTo = $replyTo;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getQueue(): string
    {
        return $this->queue;
    }

    public function setQueue(string $queueName): void
    {
        $this->queue = $queueName;
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
        return new static($rawRoute['name'], $rawRoute['queue'] ?? null, $rawRoute['replyTo'] ?? null);
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
