<?php

namespace Abc\Job;

use Abc\ApiProblem\ApiProblem;
use Abc\ApiProblem\ApiProblemException;
use Abc\Job\Util\ResultArray;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;

class JobClient extends BaseClient implements JobServerInterface
{
    /**
     * @var HttpJobClient
     */
    private $client;

    public function __construct(HttpJobClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param Filter|null $filter
     * @return array
     * @throws ApiProblemException
     */
    public function all(Filter $filter = null): array
    {
        try {
            $response = $this->client->all(null !== $filter ? $filter->toQueryParams() : [], [
                'http_errors' => true,
            ]);
        } catch (RequestException $e) {
            $this->throwApiProblemException($e);
        }

        return ResultArray::fromJson($response->getBody()->getContents());
    }

    /**
     * @param Job $job
     * @return Result
     * @throws ApiProblemException
     */
    public function process(Job $job): Result
    {
        try {
            $response = $this->client->process($job->toJson(), ['http_errors' => true]);
        } catch (RequestException $e) {
            $this->throwApiProblemException($e);
        }

        return Result::fromJson($response->getBody()->getContents());
    }

    /**
     * @param string $id
     * @return Result|null
     * @throws ApiProblemException
     */
    public function result(string $id): ?Result
    {
        try {
            $response = $this->client->result($id, ['http_errors' => true]);
        } catch (RequestException $e) {
            if (404 == $e->getCode()) {
                return null;
            }

            $this->throwApiProblemException($e);
        }

        return Result::fromJson($response->getBody()->getContents());
    }

    /**
     * @param string $id
     * @return Result|null null if no job with the given id exists
     * @throws ApiProblemException
     */
    public function restart(string $id): ?Result
    {
        try {
            $response = $this->client->restart($id, ['http_errors' => true]);
        } catch (RequestException $e) {
            if (404 == $e->getCode()) {
                return null;
            }

            $this->throwApiProblemException($e);
        }

        return Result::fromJson($response->getBody()->getContents());
    }

    /**
     * @param string $id
     * @return bool|null Bool if successful, null if no job with the given id exists
     * @throws ApiProblemException
     */
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

            $this->throwApiProblemException($e);
        }

        return true;
    }

    /**
     * @param string $id
     * @return bool|null True if successful, null if no job with the given id exists
     * @throws ApiProblemException
     */
    public function delete(string $id): ?bool
    {
        try {
            $this->client->delete($id, ['http_errors' => true]);
        } catch (RequestException $e) {
            if (404 == $e->getCode()) {
                return null;
            }

            $this->throwApiProblemException($e);
        }

        return true;
    }
}
