<?php

namespace Abc\Job;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class BaseHttpClient
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var array
     */
    protected $defaultOptions;

    public function __construct($baseUrl, ClientInterface $client, array $defaultOptions = [])
    {
        $lastStr = substr($baseUrl, -1);
        if ('/' == $lastStr) {
            $baseUrl = substr($baseUrl, 0, -1);
        }

        $this->baseUrl = $baseUrl;
        $this->client = $client;
        $this->defaultOptions = array_merge([
            'base_uri' => $baseUrl,
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ], $defaultOptions);
    }

    protected function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        return $this->client->request($method, $uri, $this->buildOptions($options));
    }

    protected function buildOptions(array $options): array
    {
        return array_merge($this->defaultOptions, $options);
    }
}
