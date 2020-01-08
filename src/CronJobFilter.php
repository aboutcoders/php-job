<?php

namespace Abc\Job;

class CronJobFilter
{
    public static function fromQueryString(?string $query): self
    {
        return new static();
    }

    public function toQueryParams(): array
    {
        // fixme: to be implemented
        return [];
    }
}
