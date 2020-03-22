<?php

namespace Abc\Job\Client;

use Psr\Http\Message\ResponseInterface;

class BrokerHttpClient extends AbstractHttpClient
{
    public function setup(string $id, array $requestOptions = []): ResponseInterface
    {
        return $this->request('post', sprintf('broker/%s/setup', $id), $requestOptions);
    }
}
