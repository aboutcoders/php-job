<?php

namespace Abc\Job\Tests\Client;

use Abc\Job\Client\RouteHttpClient;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class RouteHttpClientTest extends TestCase
{
    /**
     * @var ClientInterface|MockObject
     */
    private $clientMock;

    /**
     * @var RouteHttpClient
     */
    private $subject;

    private $defaultHeaders;

    public function setUp(): void
    {
        $this->clientMock = $this->createMock(ClientInterface::class);

        $this->subject = new RouteHttpClient('http://domain.tld/path/', $this->clientMock);

        $this->defaultHeaders = [
            'base_uri' => 'http://domain.tld/path',
            'http_errors' => false,
            'headers' => ['Content-Type' => 'application/json'],
        ];
    }

    public function testAll()
    {
        $response = $this->createMock(ResponseInterface::class);

        $expectedOptions = array_merge([
            'some' => 'value',
        ], $this->defaultHeaders);

        $this->clientMock->expects($this->once())->method('request')->with('get', 'route', $expectedOptions)->willReturn($response);

        $this->assertSame($response, $this->subject->all(['some' => 'value']));
    }

    public function testAdd()
    {
        $response = $this->createMock(ResponseInterface::class);

        $expectedOptions = array_merge([
            'body' => 'someJson',
            'some' => 'value',
        ], $this->defaultHeaders);

        $this->clientMock->expects($this->once())->method('request')->with('post', 'route', $expectedOptions)->willReturn($response);

        $this->assertSame($response, $this->subject->add('someJson', ['some' => 'value']));
    }
}
