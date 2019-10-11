<?php

namespace Abc\Job;

use Abc\ApiProblem\ApiProblem;
use Abc\ApiProblem\ApiProblemException;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;

class BaseClient
{
    /**
     * @param RequestException $e
     * @throws ApiProblemException
     */
    protected function throwApiProblemException(RequestException $e)
    {
        try {
            $apiProblem = ApiProblem::fromJson($e->getResponse()->getBody());
        } catch (InvalidArgumentException $invalidArgumentException) {
            $apiProblem = new ApiProblem(HttpJobServer::TYPE_URL.'/'.'internal-error', 'Internal Server Error', 500, 'An internal server error occurred', $e->getRequest()->getUri());
        }

        throw new ApiProblemException($apiProblem);
    }
}
