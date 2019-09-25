<?php

namespace Abc\Job;

use Abc\ApiProblem\ApiProblem;
use Abc\ApiProblem\ApiProblemException;
use Abc\Job\Util\ResultArray;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;

class Client implements ServerInterface
{
    /**
     * @var HttpClient
     */
    private $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    public function find(Filter $filter = null): array
    {
        try {
            $response = $this->client->find(['http_errors' => true]);
        } catch (RequestException $e) {
            throw $this->createApiProblemException($e);
        }

        return ResultArray::fromJson($response->getBody()->getContents());
    }

    public function process(Job $job): Result
    {
        try {
            $response = $this->client->process($job->toJson(), ['http_errors' => true]);
        } catch (RequestException $e) {
            throw $this->createApiProblemException($e);
        }

        return Result::fromJson($response->getBody()->getContents());
    }

    public function result(string $id): ?Result
    {
        try {
            $response = $this->client->result($id, ['http_errors' => true]);
        } catch (RequestException $e) {
            if (404 == $e->getCode()) {
                return null;
            }

            throw $this->createApiProblemException($e);
        }

        return Result::fromJson($response->getBody()->getContents());
    }

    public function restart(string $id): ?Result
    {
        try {
            $response = $this->client->restart($id, ['http_errors' => true]);
        } catch (RequestException $e) {
            if (404 == $e->getCode()) {
                return null;
            }

            throw $this->createApiProblemException($e);
        }

        return Result::fromJson($response->getBody()->getContents());
    }

    public function cancel(string $id): ?bool
    {
        try {
            $this->client->cancel($id, ['http_errors' => true]);
        } catch (RequestException $e) {
            if (404 == $e->getCode()) {
                return null;
            }

            if (406 == $e->getCode()) {
                return false;
            }

            throw $this->createApiProblemException($e);
        }

        return true;
    }

    public function delete(string $id): bool
    {
        try {
            $this->client->delete($id, ['http_errors' => true]);
        } catch (RequestException $e) {
            if (404 == $e->getCode()) {
                return null;
            }

            throw $this->createApiProblemException($e);
        }

        return true;
    }

    private function createApiProblemException(RequestException $e)
    {
        try {
            $apiProblem = ApiProblem::fromJson($e->getResponse()->getBody());
        } catch (InvalidArgumentException $invalidArgumentException) {
            $apiProblem = new ApiProblem(HttpServer::TYPE_URL.'/'.'internal-error', 'Internal Server Error', 500, 'An internal server error occurred', $e->getRequest()->getUri());
        }

        return new ApiProblemException($apiProblem);
    }
}
