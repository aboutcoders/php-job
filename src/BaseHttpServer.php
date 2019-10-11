<?php

namespace Abc\Job;

use Abc\ApiProblem\ApiProblem;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

abstract class BaseHttpServer
{
    const TYPE_URL = 'https://aboutcoders.com/abc-job/problem/';

    protected static $headers_ok = ['Content-Type' => 'application/json'];

    protected static $headers_problem = ['Content-Type' => 'application/problem+json'];

    protected function createInvalidParamResponse(array $invalidParameters, $requestUri): ResponseInterface
    {
        $apiProblem = new ApiProblem(self::buildTypeUrl('invalid-parameters'), 'Your request parameters didn\'t validate.', 400, 'One or more parameters are invalid.', $requestUri);
        $apiProblem->setInvalidParams($invalidParameters);

        return $this->createProblemResponse($apiProblem);
    }

    protected function createNotFoundResponse($id, $requestUri): ResponseInterface
    {
        $apiProblem = new ApiProblem(self::buildTypeUrl('resource-not-found'), 'Resource Not Found', 404, sprintf('Job with id "%s" not found', $id), $requestUri);

        return $this->createProblemResponse($apiProblem);
    }

    protected function createProblemResponse(ApiProblem $apiProblem): ResponseInterface
    {
        return new Response($apiProblem->getStatus(), static::$headers_problem, $apiProblem->toJson());
    }

    protected static function buildTypeUrl(string $problem): string
    {
        return self::TYPE_URL.$problem;
    }
}
