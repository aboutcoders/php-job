<?php

namespace Abc\Job\Model;

use Abc\Job\Type;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

class Job implements JobInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string|null
     */
    protected $type;

    /**
     * @var JobInterface|null
     */
    protected $parent;

    /**
     * @var ArrayCollection
     */
    protected $children;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $status;

    /**
     * @var string|null
     */
    protected $input;

    /**
     * @var string|null
     */
    protected $output;

    /**
     * @var bool
     */
    protected $allowFailure;

    /**
     * @var string|null
     */
    protected $externalId;

    /**
     * @var int
     */
    protected $restarts;

    /**
     * @var float
     */
    protected $processingTime;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @var DateTime
     */
    protected $updatedAt;

    /**
     * @var DateTime
     */
    protected $completedAt;

    public function __construct()
    {
        $this->processingTime = 0.0;
        $this->status = static::WAITING;
        $this->allowFailure = false;
        $this->restarts = 0;
        $this->children = new ArrayCollection();
    }

    public static function create(): JobInterface
    {
        return new static();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getType(): Type
    {
        return new Type($this->type);
    }

    public function setType(Type $type): void
    {
        $this->type = (string)$type;
    }

    public function getRoot(): JobInterface
    {
        $current = $this;
        while (null != $current->getParent()) {
            $current = $current->getParent();
        }

        return $current;
    }

    public function getParent(): ?JobInterface
    {
        return $this->parent;
    }

    public function setParent(Job $parent): void
    {
        $this->parent = $parent;
    }

    public function hasParent(): bool
    {
        return null != $this->parent;
    }

    /**
     * @return JobInterface[]
     */
    public function getChildren(): array
    {
        return $this->children->getValues();
    }

    public function addChild(JobInterface $job): void
    {
        if ($this->children->contains($job)) {
            return;
        }

        $job->setParent($this);

        if (Type::SEQUENCE() == $this->getType()) {
            $job->setPosition(count($this->children) + 1);
        }

        $this->children->add($job);
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getInput(): ?string
    {
        return $this->input;
    }

    public function setInput(?string $input): void
    {
        $this->input = $input;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function setOutput(?string $output): void
    {
        $this->output = $output;
    }

    public function isAllowFailure(): bool
    {
        return $this->allowFailure;
    }

    public function setAllowFailure(bool $allowFailure): void
    {
        $this->allowFailure = $allowFailure;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(?string $externalId): void
    {
        $this->externalId = $externalId;
    }

    public function setRestarts(int $number): void
    {
        $this->restarts = $number;
    }

    public function getRestarts(): int
    {
        return $this->restarts;
    }

    public function getProcessingTime(): float
    {
        return $this->processingTime;
    }

    public function setProcessingTime(float $value): void
    {
        $this->processingTime = $value;
    }

    public function addProcessingTime(float $value): void
    {
        $this->processingTime += $value;
    }

    public function getCompletedAt(): ?DateTime
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?DateTime $completedAt): void
    {
        $this->completedAt = $completedAt;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function __toString()
    {
        return $this->getId();
    }

    public function __clone()
    {
        $this->id = null;
    }
}
