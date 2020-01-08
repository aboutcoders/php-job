<?php

namespace Abc\Job\Client;

use Psr\Http\Message\ResponseInterface;

class CronJobHttpClient extends AbstractHttpClient
{
    private static $endpoint = 'cronjob';

    public function list(array $queryParams = [], array $options = []): ResponseInterface
    {
        $options['query'] = $queryParams;

        return $this->request('get', static::$endpoint, $options);
    }

    public function find(string $id, array $options = []): ResponseInterface
    {
        return $this->request('get', sprintf('%s/%s', static::$endpoint, $id), $options);
    }

    public function create(string $json, array $options = []): ResponseInterface
    {
        $options['body'] = $json;

        return $this->request('post', sprintf('%s', static::$endpoint), $options);
    }

    public function update(string $id, string $json, array $options = []): ResponseInterface
    {
        $options['body'] = $json;

        return $this->request('put', sprintf('%s/%s', static::$endpoint, $id), $options);
    }

    public function delete(string $id, array $options = []): ResponseInterface
    {
        return $this->request('delete', sprintf('%s/%s', static::$endpoint, $id), $options);
    }
}
