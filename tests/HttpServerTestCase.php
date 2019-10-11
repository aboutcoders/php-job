<?php

namespace Abc\Job\Tests;

use Abc\Job\HttpJobServer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

abstract class HttpServerTestCase extends TestCase
{
    protected function assertStdJsonResponseHeader(ResponseInterface $response)
    {
        $this->assertEquals(['application/json'], $response->getHeader('Content-Type'));
    }

    protected function assertProblemJsonResponseHeader(ResponseInterface $response)
    {
        $this->assertEquals(['application/problem+json'], $response->getHeader('Content-Type'));
    }

    protected function assertStatusCode(int $statusCode, ResponseInterface $response)
    {
        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    protected function assertServerErrorResponse(ResponseInterface $response)
    {
        $this->assertStatusCode(500, $response);
        $this->assertProblemJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals(HttpJobServer::TYPE_URL.'internal-error', $data['type']);
        $this->assertEquals('Internal Server Error', $data['title']);
        $this->assertEquals(500, $data['status']);
        $this->assertEquals('An internal server error occurred', $data['detail']);
        $this->assertEquals('requestUri', $data['instance']);
    }

    protected function assertInvalidParameterResponse(ResponseInterface $response)
    {
        $this->assertStatusCode(400, $response);
        $this->assertProblemJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals(HttpJobServer::TYPE_URL.'invalid-parameters', $data['type']);
        $this->assertEquals('Your request parameters didn\'t validate.', $data['title']);
        $this->assertEquals(400, $data['status']);
        $this->assertEquals('One or more parameters are invalid.', $data['detail']);
        $this->assertEquals('requestUri', $data['instance']);
        $this->assertEquals('name', $data['invalid-params'][0]['name']);
        $this->assertEquals('reason', $data['invalid-params'][0]['reason']);
        $this->assertEquals('value', $data['invalid-params'][0]['value']);
    }
}
