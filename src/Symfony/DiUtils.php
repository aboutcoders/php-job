<?php

namespace Abc\Job\Symfony;

class DiUtils
{
    public static function create(): self
    {
        return new self();
    }

    public function parameter(string $name): string
    {
        $fullName = $this->format($name);

        return "%$fullName%";
    }

    public function format(string $serviceName): string
    {
        return sprintf('abc.job.%s', $serviceName);
    }
}
