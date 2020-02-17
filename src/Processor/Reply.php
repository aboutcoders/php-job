<?php

namespace Abc\Job\Processor;

class Reply
{
    /**
     * @var string
     */
    private $jobId;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $output;

    /**
     * @var float
     */
    private $processingTime = 0;

    /**
     * @var int
     */
    private $createdTimestamp;

    public function __construct(
        string $jobId,
        string $status,
        ?string $output = null,
        ?float $processingTime = null,
        ?int $createdTimestamp = null
    ) {
        $this->jobId = $jobId;
        $this->status = $status;
        $this->output = $output;
        $this->processingTime = $processingTime;
        $this->createdTimestamp = $createdTimestamp ?? time();
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getProcessingTime(): ?float
    {
        return $this->processingTime;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function getCreatedTimestamp(): int
    {
        return $this->createdTimestamp;
    }

    public static function fromArray($data): self
    {
        return new static(
            $data['jobId'],
            $data['status'],
            $data['output'] ?? null,
            $data['processingTime'] ?? null,
            $data['createdTimestamp'] ?? null
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public static function fromJson(string $json): self
    {
        $data = @json_decode($json, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON');
        }

        return static::fromArray($data);
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
