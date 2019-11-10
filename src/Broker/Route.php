<?php

namespace Abc\Job\Broker;

use OpenApi\Annotations as OA;

/**
 *  * @OA\Schema(
 *     description="A Route defines queue and replyTo queue of a job"
 * )
 */
class Route
{
    /**
     * @OA\Property(
     *     description="The job name"
     * )
     *
     * @var string
     */
    protected $name;

    /**
     * @OA\Property(
     *     description="The queue name"
     * )
     *
     * @var string
     */
    protected $queue;

    /**
     * @OA\Property(
     *     description="The name of the replyTo queue"
     * )
     *
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
