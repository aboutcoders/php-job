<?php

namespace Abc\Job\Model;

use Abc\Scheduler\Model\ScheduleTrait;
use DateTime;

class Schedule implements ScheduleInterface
{
    use ScheduleTrait;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $jobJson;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @var DateTime
     */
    protected $updatedAt;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id)
    {
        $this->id = $id;
    }

    public function getJobJson(): ?string
    {
        return $this->jobJson;
    }

    public function setJobJson(?string $jobJson)
    {
        $this->jobJson = $jobJson;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }
}
