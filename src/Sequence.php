<?php

namespace Abc\Job;

class Sequence extends Job
{
    public function __construct(string $name = null, array $children = [], string $externalId = null)
    {
        parent::__construct(Type::SEQUENCE(), $name, null, $children, $externalId);
    }
}
