<?php

namespace Abc\Job;

class CronJobFilter
{
    public static function fromQueryString(?string $query): self
    {
        return new static();
    }
}
