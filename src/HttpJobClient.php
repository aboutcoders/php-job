<?php

namespace Abc\Job;

use Psr\Http\Message\ResponseInterface;

class HttpJobClient extends BaseHttpClient
{
    private static $endpoint = 'job';

    public function all(array $queryParams = [], array $options = []): ResponseInterface
    {
        $options['query'] = $queryParams;

        return $this->request('get', static::$endpoint, $options);
    }

    public function process(string $json, array $options = []): ResponseInterface
    {
        $options['body'] = $json;

        return $this->request('post', static::$endpoint, $options);
    }

    public function restart(string $id, array $options = []): ResponseInterface
    {
        return $this->request('put', sprintf('%s/%s/restart', static::$endpoint, $id), $options);
    }

    public function cancel(string $id, array $options = []): ResponseInterface
    {
        return $this->request('put', sprintf('%s/%s/cancel', static::$endpoint, $id), $options);
    }

    public function result(string $id, array $options = []): ResponseInterface
    {
        return $this->request('get', sprintf('%s/%s', static::$endpoint, $id), $options);
    }

    public function delete(string $id, array $options = []): ResponseInterface
    {
        return $this->request('delete', sprintf('%s/%s', static::$endpoint, $id), $options);
    }
}
