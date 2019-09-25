<?php

namespace Abc\Job;

use Abc\ApiProblem\InvalidParameter;

interface ValidatorInterface
{
    /**
     * @param string $json
     * @param string $class
     * @return InvalidParameter[]
     */
    public function validate(string $json, string $class): array;
}
