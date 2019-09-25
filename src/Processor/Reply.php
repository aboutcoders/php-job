<?php

namespace Abc\Job\Processor;

class Reply
{
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
        string $status,
        string $output = null,
        float $processingTime = 0.0,
        int $createdTimestamp = null
    ) {
        $this->output = $output;
        $this->status = $status;
        $this->processingTime = $processingTime;
        $this->createdTimestamp = $createdTimestamp ?? time();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getProcessingTime(): float
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

    public static function fromJson(string $json): self
    {
        $data = @json_decode($json, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON');
        }

        if (! isset($data['status'])) {
            throw new \InvalidArgumentException('Expected key "status" not found in JSON');
        }

        return new self(...[
            $data['status'],
            $data['output'] ?? null,
            $data['processingTime'] ?? 0.0,
            $data['createdTimestamp'] ?? null,
        ]);
    }
}
