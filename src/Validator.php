<?php

namespace Abc\Job;

use Abc\ApiProblem\InvalidParameter;
use Abc\Job\Broker\Route;
use JsonSchema\Exception\JsonDecodingException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class Validator implements ValidatorInterface
{
    /**
     * @var \JsonSchema\Validator
     */
    private $validator;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    private static $schemas = [
        Job::class => 'job.json',
        JobFilter::class => 'filter.json',
        Route::class => 'route.json',
        CronJob::class => 'cronJob.json',
    ];

    public function __construct()
    {
        $this->validator = new \JsonSchema\Validator();
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param string $json
     * @param string $class
     * @return InvalidParameter[]
     * @throws JsonDecodingException
     */
    public function validate(string $json, string $class): array
    {
        if (! array_key_exists($class, static::$schemas)) {
            throw new \InvalidArgumentException(sprintf('The class %s is not supported', $class));
        }

        switch ($class) {
            case JobFilter::class:
            case Job::class:
            case CronJob::class:
            case Route::class:
                $data = json_decode($json);
                break;
        }

        if (JSON_ERROR_NONE < $error = json_last_error()) {
            $decodingException = new JsonDecodingException($error);

            throw new InvalidJsonException($decodingException->getMessage(), $decodingException->getCode());
        }

        $this->validator->validate($data, (object) ['$ref' => 'file://'.realpath(__DIR__.'/schema/'.static::$schemas[$class])]);

        $invalidParameters = [];
        if (! $this->validator->isValid()) {
            foreach ($this->validator->getErrors() as $error) {
                $invalidParameters[] = $this->createInvalidParam($data, $error);
            }
        }

        return $invalidParameters;
    }

    private function createInvalidParam($data, array $error): InvalidParameter
    {
        $value = null;
        if (null != $error['property']) {
            $value = $this->propertyAccessor->isReadable($data, $error['property']) ? $this->propertyAccessor->getValue($data, $error['property']) : null;

            if (is_object($value) || is_array($value)) {
                $value = json_encode($value);
            }
        }

        return new InvalidParameter($error['property'], $error['message'], $value);
    }
}
