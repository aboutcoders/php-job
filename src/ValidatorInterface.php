<?php

namespace Abc\Job;

use Abc\ApiProblem\InvalidParameter;

interface ValidatorInterface
{
    /**
     * @param string $json
     * @param string $class
     * @return InvalidParameter[]
     * @throws \InvalidArgumentException In case of a json decoding exception
     */
    public function validate(string $json, string $class): array;
}
