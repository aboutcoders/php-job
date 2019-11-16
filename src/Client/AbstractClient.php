<?php

namespace Abc\Job\Client;

use Abc\ApiProblem\ApiProblem;
use Abc\ApiProblem\ApiProblemException;
use Abc\Job\Controller\AbstractController;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractClient
{
    /**
     * @param ResponseInterface $response
     * @throws ApiProblemException
     */
    protected function validateResponse(ResponseInterface $response)
    {
        if (400 <= $response->getStatusCode()) {
            $this->throwApiProblemException($response);
        }
    }

    /**
     * @param ResponseInterface $response
     * @throws ApiProblemException
     */
    protected function throwApiProblemException(ResponseInterface $response)
    {
        try {
            $apiProblem = ApiProblem::fromJson($response->getBody()->getContents());
        } catch (InvalidArgumentException $invalidArgumentException) {
            $apiProblem = new ApiProblem(AbstractController::TYPE_URL.'/'.'internal-error', 'Internal Server Error', 500, 'An internal server error occurred');
        }

        throw new ApiProblemException($apiProblem);
    }
}