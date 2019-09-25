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
     * @var string
     */
    protected $type;

    /**
     * @var JobInterface
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
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $input;

    /**
     * @var string
     */
    protected $output;

    /**
     * @var bool
     */
    protected $allowFailure;

    /**
     * @var float
     */
    protected $processingTime;

    /**
     * @var string
     */
    protected $externalId;

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
        $this->children = new ArrayCollection();
        $this->allowFailure = false;
    }

    public static function create(): JobInterface
    {
        return new static();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id)
    {
        $this->id = $id;
    }

    public function getType(): Type
    {
        return new Type($this->type);
    }

    public function setType(Type $type)
    {
        $this->type = (string) $type;
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

    public function setParent(Job $parent)
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

    public function addChild(JobInterface $job)
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

    public function setPosition(int $position)
    {
        $this->position = $position;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name)
    {
        $this->name = $name;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    public function getInput(): ?string
    {
        return $this->input;
    }

    public function setInput(?string $input)
    {
        $this->input = $input;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function setOutput(?string $output)
    {
        $this->output = $output;
    }

    public function isAllowFailure(): bool
    {
        return $this->allowFailure;
    }

    public function setAllowFailure(bool $allowFailure)
    {
        $this->allowFailure = $allowFailure;
    }

    public function setProcessingTime(float $value)
    {
        $this->processingTime = $value;
    }

    public function getProcessingTime(): float
    {
        return $this->processingTime;
    }

    public function addProcessingTime(float $value)
    {
        $this->processingTime += $value;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(?string $externalId)
    {
        $this->externalId = $externalId;
    }

    public function setCompletedAt(?DateTime $completedAt)
    {
        $this->completedAt = $completedAt;
    }

    public function getCompletedAt(): ?DateTime
    {
        return $this->completedAt;
    }

    public function setCreatedAt(?DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
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
