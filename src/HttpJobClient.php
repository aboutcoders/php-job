<?php

namespace Abc\Job;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    protected static $basePath = 'job';

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var array
     */
    private $defaultOptions;

    public function __construct($baseUrl, ClientInterface $client, array $defaultOptions)
    {
        $this->baseUrl = $baseUrl;
        $this->client = $client;
        $this->defaultOptions = array_merge([
            'http_errors' => false,
            'headers' => [
                'content-type' => 'application/json'
            ]
        ], $defaultOptions);
    }

    public function find(array $options = []): ResponseInterface
    {
        return $this->client->request('get', static::$basePath, $this->buildOptions($options));
    }

    public function process(string $json, array $options = []): ResponseInterface
    {
        $options = array_merge($this->buildOptions($options), ['body' => $json]);

        return $this->client->request('create', static::$basePath, $options);
    }

    public function restart(string $id, array $options = []): ResponseInterface
    {
        return $this->client->request('put', sprintf('%s/%s/restart', static::$basePath, $id), $this->buildOptions($options));
    }

    public function cancel(string $id, array $options = []): ResponseInterface
    {
        return $this->client->request('put', sprintf('%s/%s/cancel', static::$basePath, $id), $this->buildOptions($options));
    }

    public function result(string $id, array $options = []): ResponseInterface
    {
        return $this->client->request('get', sprintf('%s/%s', static::$basePath, $id), $this->buildOptions($options));
    }

    public function delete(string $id, array $options = []): ResponseInterface
    {
        return $this->client->request('delete', sprintf('%s/%s', static::$basePath, $id), $this->buildOptions($options));
    }

    private function buildOptions(array $options): array
    {
        return array_merge($this->defaultOptions, $options);
    }
}
