<?php

namespace Abc\Job;

use Abc\Job\Model\Job;
use Abc\Job\Model\JobInterface;
use Abc\Job\Model\JobManager;
use DateTime;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     description="The result of a job"
 * )
 */
class Result
{
    /**
     * @OA\Property(
     *     description="The job type",
     *     enum={"Job", "Batch", "Sequence"},
     * )
     *
     * @var string
     */
    private $type;

    /**
     * @OA\Property(
     *     format="uuid"
     * )
     *
     * @var string
     */
    private $id;

    /**
     * @OA\Property(
     *     description="The job name"
     * )
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(
     *     description="The job type",
     *     enum={"waiting", "scheduled", "running", "complete", "failed", "cancelled"},
     * )
     *
     * @var string
     */
    private $status;

    /**
     * @OA\Property(
     *     description="The job input"
     * )
     *
     * @var string
     */
    private $input;

    /**
     * @OA\Property(
     *     description="The job output"
     * )
     *
     * @var string
     */
    private $output;

    /**
     * @OA\Property(
     *     description="Whether a job in a Sequence or Batch may fail"
     * )
     *
     * @var bool
     */
    private $allowFailure;

    /**
     * @OA\Property
     *
     * @var float
     */
    private $processingTime;

    /**
     * @OA\Property(
     *     format="uuid",
     *     description="An external identifier of the job"
     * )
     *
     * @var string
     */
    private $externalId;

    /**
     * @var int
     */
    protected $restarts;

    /**
     * @OA\Property(
     *     type="array",
     *         @OA\Items(ref="#/components/schemas/Result")
     *     )
     * )
     *
     * @var Result[]
     */
    private $children = [];

    /**
     * @OA\Property(
     *     description="The datetime the job completed",
     *     format="date-time"
     * )
     *
     * @var DateTime
     */
    private $completed;

    /**
     * @OA\Property(
     *     description="The datetime the job was updated",
     *     format="date-time"
     * )
     *
     * @var DateTime
     */
    private $updated;

    /**
     * @OA\Property(
     *     description="The datetime the job was created",
     *     format="date-time"
     * )
     *
     * @var DateTime
     */
    private $created;

    public function __construct(JobInterface $job)
    {
        $this->type = (string) $job->getType();
        $this->id = $job->getId();
        $this->name = $job->getName();
        $this->status = $job->getStatus();
        $this->input = $job->getInput();
        $this->output = $job->getOutput();
        $this->allowFailure = $job->isAllowFailure();
        $this->processingTime = $job->getProcessingTime();
        $this->externalId = $job->getExternalId();
        $this->restarts = $job->getRestarts();
        $this->completed = $job->getCompletedAt();
        $this->updated = $job->getUpdatedAt();
        $this->created = $job->getCreatedAt();

        foreach (JobManager::sortByPosition($job->getChildren()) as $child) {
            $this->children[] = new self($child);
        }
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getInput(): ?string
    {
        return $this->input;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function isAllowFailure(): bool
    {
        return $this->allowFailure;
    }

    /**
     * @return self[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function getProcessingTime(): float
    {
        return $this->processingTime;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function getRestarts(): int
    {
        return $this->restarts;
    }

    public function getCompleted(): ?DateTime
    {
        return $this->completed;
    }

    public function getUpdated(): DateTime
    {
        return $this->updated;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function toArray(): array
    {
        $array = [
            'type' => (string) $this->getType(),
            'id' => $this->getId(),
            'name' => $this->getName(),
            'status' => $this->getStatus(),
            'input' => $this->getInput(),
            'output' => $this->getOutput(),
            'allowFailure' => $this->isAllowFailure(),
            'processingTime' => $this->getProcessingTime(),
            'externalId' => $this->getExternalId(),
            'restarts' => $this->getRestarts(),
            'completed' => null == $this->getCompleted() ? null : $this->getCompleted()->format('c'),
            'updated' => null == $this->getUpdated() ? null : $this->getUpdated()->format('c'),
            'created' => null == $this->getCreated() ? null : $this->getCreated()->format('c'),
            'children' => array_map(function (Result $child) {
                return $child->toArray();
            }, $this->getChildren()),
        ];

        return $array;
    }

    public static function fromArray(array $data): Result
    {
        return new static(JobManager::fromArray(Job::create(), $data));
    }

    public function toJson(): string
    {
        return json_encode((object) $this->toArray());
    }

    public static function fromJson(string $json): Result
    {
        return static::fromArray(json_decode($json, true));
    }
}
