<?php

namespace Abc\Job\Client;

use Abc\ApiProblem\ApiProblemException;
use Abc\Job\JobFilter;
use Abc\Job\Job;
use Abc\Job\JobServerInterface;
use Abc\Job\Result;
use Abc\Job\Util\ResultArray;

class JobClient extends AbstractClient implements JobServerInterface
{
    /**
     * @var JobHttpClient
     */
    private $client;

    public function __construct(JobHttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param JobFilter|null $filter
     * @return array
     * @throws ApiProblemException
     */
    public function list(JobFilter $filter = null): array
    {
        $response = $this->client->list(null !== $filter ? $filter->toQueryParams() : []);

        $this->validateResponse($response);

        return ResultArray::fromJson($response->getBody()->getContents());
    }

    /**
     * @param Job $job
     * @return Result
     * @throws ApiProblemException
     */
    public function process(Job $job): Result
    {
        $response = $this->client->process($job->toJson());

        $this->validateResponse($response);

        return Result::fromJson($response->getBody()->getContents());
    }

    /**
     * @param string $id
     * @return Result|null
     * @throws ApiProblemException
     */
    public function result(string $id): ?Result
    {
        $response = $this->client->result($id);

        if (404 == $response->getStatusCode()) {
            return null;
        }

        $this->validateResponse($response);

        return Result::fromJson($response->getBody()->getContents());
    }

    /**
     * @param string $id
     * @return Result|null null if no job with the given id exists
     * @throws ApiProblemException
     */
    public function restart(string $id): ?Result
    {
        $response = $this->client->restart($id);

        if (404 == $response->getStatusCode()) {
            return null;
        }

        $this->validateResponse($response);

        return Result::fromJson($response->getBody()->getContents());
    }

    /**
     * @param string $id
     * @return bool|null Bool if successful, null if no job with the given id exists
     * @throws ApiProblemException
     */
    public function cancel(string $id): ?bool
    {
        $response = $this->client->cancel($id);

        if (404 == $response->getStatusCode()) {
            return null;
        }

        if (406 == $response->getStatusCode()) {
            return false;
        }

        $this->validateResponse($response);

        return true;
    }

    /**
     * @param string $id
     * @return bool|null True if successful, null if no job with the given id exists
     * @throws ApiProblemException
     */
    public function delete(string $id): ?bool
    {
        $response = $this->client->delete($id);

        if (404 == $response->getStatusCode()) {
            return null;
        }

        $this->validateResponse($response);

        return true;
    }
}
