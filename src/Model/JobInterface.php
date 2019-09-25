<?php

namespace Abc\Job\Model;

use Abc\Job\Status;
use Abc\Job\Type;
use DateTime;

interface JobInterface extends Status
{
    public static function create(): JobInterface;

    public function getId(): ?string;

    public function setId(?string $ticket);

    public function getType(): Type;

    public function setType(Type $type);

    public function getRoot(): JobInterface;

    public function getParent(): ?JobInterface;

    public function setParent(Job $parent);

    /**
     * @return self[]
     */
    public function getChildren(): array;

    public function addChild(JobInterface $job);

    public function hasParent(): bool;

    public function getPosition(): ?int;

    public function setPosition(int $position);

    public function getName(): ?string;

    public function setName(?string $name);

    public function getStatus(): string;

    public function setStatus(string $status);

    public function getInput(): ?string;

    public function setInput(?string $input);

    public function getOutput(): ?string;

    public function setOutput(?string $output);

    public function isAllowFailure(): bool;

    public function setAllowFailure(bool $allowFailure);

    public function getProcessingTime(): float;

    public function setProcessingTime(float $value);

    public function addProcessingTime(float $value);

    public function getExternalId(): ?string;

    public function setExternalId(?string $externalId);

    public function getCompletedAt(): ?DateTime;

    public function setCompletedAt(?DateTime $terminatedAt);

    public function getUpdatedAt(): ?DateTime;

    public function setUpdatedAt(?DateTime $createdAt);

    public function getCreatedAt(): ?DateTime;

    public function setCreatedAt(?DateTime $createdAt);
}
