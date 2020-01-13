<?php

namespace Abc\Job;

class Batch extends Job
{
    public function __construct(string $name = null, array $children = [], string $externalId = null)
    {
        parent::__construct(Type::BATCH(), $name, null, $children, $externalId);
    }
}
