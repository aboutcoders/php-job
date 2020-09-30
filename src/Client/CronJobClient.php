<?php

namespace Abc\Job\Client;

use Abc\ApiProblem\ApiProblemException;
use Abc\Job\CronJob;
use Abc\Job\CronJobFilter;
use Abc\Job\Job;
use Abc\Job\Util\CronJobArray;
use Abc\Scheduler\ConcurrencyPolicy;

class CronJobClient extends AbstractClient
{
    /**
     * @var CronJobHttpClient
     */
    private $client;

    public function __construct(CronJobHttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param  CronJobFilter|null  $filter
     * @return array
     * @throws ApiProblemException
     */
    public function list(CronJobFilter $filter = null): array
    {
        $response = $this->client->list(null !== $filter ? $filter->toQueryParams() : []);

        $this->validateResponse($response);

        return CronJobArray::fromJson($response->getBody()->getContents());
    }

    /**
     * @param  string  $id
     * @return CronJob|null
     * @throws ApiProblemException
     */
    public function find(string $id): ?CronJob
    {
        $response = $this->client->find($id);

        if (404 == $response->getStatusCode()) {
            return null;
        }

        $this->validateResponse($response);

        return \Abc\Job\Model\CronJob::fromJson($response->getBody()->getContents());
    }

    /**
     * @param  string  $scheduleExpression
     * @param  Job  $job
     * @param  ConcurrencyPolicy|null $policy Default is ConcurrencyPolicy::ALLOW()
     * @return CronJob The managed cron job
     * @throws ApiProblemException
     */
    public function create(string $scheduleExpression, Job $job, ConcurrencyPolicy $policy = null): CronJob
    {
        $data = $job->toArray();
        $data['schedule'] = $scheduleExpression;

        if (null !== $policy) {
            $data['concurrencyPolicy'] = (string)$policy;
        }

        $response = $this->client->create(json_encode((object)$data));

        $this->validateResponse($response);

        return \Abc\Job\Model\CronJob::fromJson($response->getBody()->getContents());
    }

    public function update(string $id, string $scheduleExpression, Job $job): CronJob
    {
        $data = $job->toArray();
        $data['schedule'] = $scheduleExpression;

        $response = $this->client->update($id, json_encode((object)$data));

        $this->validateResponse($response);

        return \Abc\Job\Model\CronJob::fromJson($response->getBody()->getContents());
    }

    /**
     * @param  string  $id
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
