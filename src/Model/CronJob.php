<?php

namespace Abc\Job\Model;

use Abc\Job\Job;
use Abc\Job\Util\DateUtil;
use DateTime;

class CronJob implements CronJobInterface
{
    /**
     * @var string|null
     */
    protected $id;

    /**
     * @var string|null
     */
    protected $schedule;

    /**
     * @var int|null
     */
    protected $scheduledTime;

    /**
     * @var \Abc\Job\Job|null
     */
    protected $job;

    /**
     * @var string|null
     */
    protected $jobJson;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var DateTime|null
     */
    protected $createdAt;

    /**
     * @var DateTime|null
     */
    protected $updatedAt;

    public function __construct(string $schedule, Job $job)
    {
        $this->schedule = $schedule;
        $this->job = $job;
    }

    public static function create(string $schedule, Job $job): CronJobInterface
    {
        return new static($schedule, $job);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getSchedule(): string
    {
        if (null == $this->schedule) {
            throw new \LogicException(sprintf('Expected the variable $schedule to be set. Either %s must call the parent constructor of %s or call setSchedule() right after instantiation.', get_class($this), self::class));
        }

        return $this->schedule;
    }

    public function setSchedule(string $schedule): void
    {
        $this->schedule = $schedule;
    }

    public function setScheduledTime(int $timestamp): void
    {
        $this->scheduledTime = $timestamp;
    }

    public function getScheduledTime(): ?int
    {
        return $this->scheduledTime;
    }

    public function getJob(): Job
    {
        if (null === $this->job) {
            if (null === $this->jobJson) {
                throw new \LogicException(sprintf('Expected the variable $jobJson to be set. Either %s must call the parent constructor of %s or call setJobJson() right after instantiation.', get_class($this), self::class));
            }

            $this->job = Job::fromJson($this->jobJson);
        }

        return $this->job;
    }

    public function setJob(Job $job): void
    {
        $this->job = $job;
    }

    public function getJobJson(): ?string
    {
        if (null != $this->job) {
            return $this->job->toJson();
        }

        return $this->jobJson;
    }

    public function setJobJson(?string $json): void
    {
        $this->jobJson = $json;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getCreated(): ?DateTime
    {
        return $this->getCreatedAt();
    }

    public function setCreatedAt(?DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdated(): ?DateTime
    {
        return $this->getUpdatedAt();
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @param string $json
     * @return self
     * @throws \InvalidArgumentException
     */
    public static function fromJson(string $json): CronJobInterface
    {
        $data = @json_decode($json, true);

        if (null === $data) {
            throw new \InvalidArgumentException('Invalid json');
        }

        return static::fromArray($data);
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public static function fromArray(array $data): CronJobInterface
    {
        if (! isset($data['schedule'])) {
            throw new \InvalidArgumentException('The property "schedule" must be set');
        }

        $schedule = $data['schedule'];
        unset($data['schedule']);

        $cronJob = static::create($schedule, Job::fromArray($data));
        $cronJob->setId($data['id'] ?? null);
        $cronJob->setCreatedAt(! isset($data['created']) ? null : DateUtil::createDate(strtotime($data['created'])));
        $cronJob->setUpdatedAt(! isset($data['updated']) ? null : DateUtil::createDate(strtotime($data['updated'])));

        return $cronJob;
    }

    public function toArray(): array
    {
        $data = [
            'id' => $this->getId(),
            'schedule' => $this->getSchedule(),
            'updated' => null == $this->getUpdated() ? null : $this->getUpdated()->format('c'),
            'created' => null == $this->getCreated() ? null : $this->getCreated()->format('c'),
        ];

        $data = array_merge(array_flip([
            'id',
            'schedule',
            'type',
            'name',
            'input',
            'allowFailure',
            'externalId',
            'updated',
            'created',
            'children',
        ]), array_merge($data, $this->getJob()->toArray()));

        foreach (['id', 'updated', 'created'] as $key) {
            if (null == $data[$key]) {
                unset($data[$key]);
            }
        }

        return $data;
    }
}
