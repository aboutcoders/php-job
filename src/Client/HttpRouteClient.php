<?php

namespace Abc\Job\Client;

use Psr\Http\Message\ResponseInterface;

class HttpRouteClient extends AbstractHttpClient
{
    private static $endpoint = 'route';

    public function add(string $json, array $options = []): ResponseInterface
    {
        $options['body'] = $json;

        return $this->request('post', static::$endpoint, $options);
    }

    public function all(array $options = []): ResponseInterface
    {
        return $this->request('get', static::$endpoint, $options);
    }
}
