<?php

namespace Abc\Job\Client;

use Abc\ApiProblem\ApiProblem;
use Abc\ApiProblem\ApiProblemException;
use Abc\Job\Controller\AbstractController;
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
        $requestBody = $response->getBody()
            ->getContents()
        ;

        try {
            $apiProblem = ApiProblem::fromJson($requestBody);
        } catch (\InvalidArgumentException $invalidArgumentException) {
            $apiProblem = new ApiProblem(
                AbstractController::TYPE_URL.'undefined',
                $response->getReasonPhrase(),
                $response->getStatusCode(),
                $requestBody
            );
        }

        throw new ApiProblemException($apiProblem);
    }
}
