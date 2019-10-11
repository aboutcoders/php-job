<?php

namespace Abc\Job\Tests;

use Abc\Job\HttpJobClient;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class HttpJobClientTest extends TestCase
{
    /**
     * @var ClientInterface|MockObject
     */
    private $clientMock;

    /**
     * @var HttpJobClient
     */
    private $subject;

    private $defaultHeaders;

    public function setUp(): void
    {
        $this->clientMock = $this->createMock(ClientInterface::class);

        $this->subject = new HttpJobClient('http://domain.tld/job/', $this->clientMock);

        $this->defaultHeaders = [
            'base_uri' => 'http://domain.tld/job',
            'http_errors' => false,
            'headers' => ['Content-Type' => 'application/json'],
        ];
    }

    public function testAll()
    {
        $response = $this->createMock(ResponseInterface::class);

        $expectedOptions = array_merge([
            'some' => 'value',
            'query' => ['filter' => 'value'],
        ], $this->defaultHeaders);

        $this->clientMock->expects($this->once())->method('request')->with('get', 'job', $expectedOptions)->willReturn($response);

        $this->assertSame($response, $this->subject->all(['filter' => 'value'], ['some' => 'value']));
    }

    public function testProcess()
    {
        $response = $this->createMock(ResponseInterface::class);

        $expectedOptions = array_merge([
            'some' => 'value',
            'body' => 'someJson',
        ], $this->defaultHeaders);

        $this->clientMock->expects($this->once())->method('request')->with('post', 'job', $expectedOptions)->willReturn($response);

        $this->assertSame($response, $this->subject->process('someJson', ['some' => 'value']));
    }

    public function testRestart()
    {
        $response = $this->createMock(ResponseInterface::class);

        $expectedOptions = array_merge([
            'some' => 'value',
        ], $this->defaultHeaders);

        $this->clientMock->expects($this->once())->method('request')->with('put', 'job/someId/restart', $expectedOptions)->willReturn($response);

        $this->assertSame($response, $this->subject->restart('someId', ['some' => 'value']));
    }

    public function testCancel()
    {
        $response = $this->createMock(ResponseInterface::class);

        $expectedOptions = array_merge([
            'some' => 'value',
        ], $this->defaultHeaders);

        $this->clientMock->expects($this->once())->method('request')->with('put', 'job/someId/cancel', $expectedOptions)->willReturn($response);

        $this->assertSame($response, $this->subject->cancel('someId', ['some' => 'value']));
    }

    public function testResult()
    {
        $response = $this->createMock(ResponseInterface::class);

        $expectedOptions = array_merge([
            'some' => 'value',
        ], $this->defaultHeaders);

        $this->clientMock->expects($this->once())->method('request')->with('get', 'job/someId', $expectedOptions)->willReturn($response);

        $this->assertSame($response, $this->subject->result('someId', ['some' => 'value']));
    }

    public function testDelete()
    {
        $response = $this->createMock(ResponseInterface::class);

        $expectedOptions = array_merge([
            'some' => 'value',
        ], $this->defaultHeaders);

        $this->clientMock->expects($this->once())->method('request')->with('delete', 'job/someId', $expectedOptions)->willReturn($response);

        $this->assertSame($response, $this->subject->delete('someId', ['some' => 'value']));
    }
}
