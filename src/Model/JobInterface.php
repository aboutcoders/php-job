<?php

namespace Abc\Job\Model;

use Abc\Job\Status;
use Abc\Job\Type;
use DateTime;

interface JobInterface extends Status
{
    public static function create(): JobInterface;

    public function getId(): ?string;

    public function setId(?string $ticket): void;

    public function getType(): Type;

    public function setType(Type $type): void;

    public function getRoot(): JobInterface;

    public function getParent(): ?JobInterface;

    public function setParent(Job $parent): void;

    /**
     * @return self[]
     */
    public function getChildren(): array;

    public function addChild(JobInterface $job): void;

    public function hasParent(): bool;

    public function getPosition(): ?int;

    public function setPosition(int $position): void;

    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getStatus(): string;

    public function setStatus(string $status): void;

    public function getInput(): ?string;

    public function setInput(?string $input): void;

    public function getOutput(): ?string;

    public function setOutput(?string $output): void;

    public function isAllowFailure(): bool;

    public function setAllowFailure(bool $allowFailure): void;

    public function getExternalId(): ?string;

    public function setExternalId(?string $externalId): void;

    public function setRestarts(int $number): void;

    public function getRestarts(): int;

    public function getProcessingTime(): float;

    public function setProcessingTime(float $value): void;

    public function addProcessingTime(float $value): void;

    public function getCompletedAt(): ?DateTime;

    public function setCompletedAt(?DateTime $terminatedAt): void;

    public function getUpdatedAt(): ?DateTime;

    public function setUpdatedAt(?DateTime $createdAt): void;

    public function getCreatedAt(): ?DateTime;

    public function setCreatedAt(?DateTime $createdAt): void;
}
