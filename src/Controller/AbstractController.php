<?php

namespace Abc\Job\Controller;

use Abc\ApiProblem\ApiProblem;
use Abc\Job\InvalidJsonException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractController
{
    const TYPE_URL = 'https://aboutcoders.com/abc-job/problem/';

    protected static $headers_ok = ['Content-Type' => 'application/json'];

    protected static $headers_problem = ['Content-Type' => 'application/problem+json'];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handles exceptions thrown by a given function.
     *
     * @param \Closure $function
     * @param string $requestUri
     * @param LoggerInterface $logger
     * @return mixed
     */
    protected function handleExceptions(\Closure $function, string $requestUri, LoggerInterface $logger)
    {
        try {
            return $function();
        } catch (InvalidJsonException $exception) {
            return $this->createInvalidJsonResponse($exception, $requestUri);
        } catch (\Exception $exception) {
            $logger->error(
                sprintf(
                    '[%s] %s [%s](code: %s) at %s line: %s',
                    get_class($this),
                    $exception->getMessage(),
                    get_class($exception),
                    $exception->getCode(),
                    $exception->getFile(),
                    $exception->getLine()
                )
            );

            $apiProblem = new ApiProblem(
                self::buildTypeUrl('internal-error'),
                'Internal Server Error',
                500,
                'An internal server error occurred',
                $requestUri
            );

            return $this->createProblemResponse($apiProblem);
        }
    }

    protected function createInvalidJsonResponse(
        InvalidJsonException $invalidJsonException,
        string $requestUri
    ): ResponseInterface {
        $apiProblem = new ApiProblem(
            self::buildTypeUrl('invalid-request-body'),
            'The request body didn\'t validate.',
            400,
            $invalidJsonException->getMessage(),
            $requestUri
        );

        return $this->createProblemResponse($apiProblem);
    }

    protected function createInvalidParamResponse(array $invalidParameters, $requestUri): ResponseInterface
    {
        $apiProblem = new ApiProblem(
            self::buildTypeUrl('invalid-parameters'),
            'Your request parameters didn\'t validate.',
            400,
            'One or more parameters are invalid.',
            $requestUri
        );
        $apiProblem->setInvalidParams($invalidParameters);

        return $this->createProblemResponse($apiProblem);
    }

    protected function createNotFoundResponse(string $id, string $resourceName, $requestUri): ResponseInterface
    {
        $apiProblem = new ApiProblem(
            self::buildTypeUrl('resource-not-found'),
            'Resource Not Found',
            404,
            sprintf('%s with id "%s" not found', $resourceName, $id),
            $requestUri
        );

        return $this->createProblemResponse($apiProblem);
    }

    protected function createProblemResponse(ApiProblem $apiProblem): ResponseInterface
    {
        return new Response($apiProblem->getStatus(), static::$headers_problem, $apiProblem->toJson());
    }

    protected static function buildTypeUrl(string $problem): string
    {
        return self::TYPE_URL . $problem;
    }
}
