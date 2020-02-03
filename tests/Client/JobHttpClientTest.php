<?php

namespace Abc\Job\Tests\Client;

use Abc\Job\Client\JobHttpClient;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class JobHttpClientTest extends TestCase
{
    /**
     * @var ClientInterface|MockObject
     */
    private $clientMock;

    /**
     * @var JobHttpClient
     */
    private $subject;

    private $defaultHeaders;

    public function setUp(): void
    {
        $this->clientMock = $this->createMock(ClientInterface::class);

        $this->subject = new JobHttpClient('http://domain.tld/job/', $this->clientMock);

        $this->defaultHeaders = [
            'base_uri' => 'http://domain.tld/job/',
            'http_errors' => false,
            'headers' => ['Content-Type' => 'application/json'],
        ];
    }

    public function testConstructFixesBaseUrl(): void
    {
        $this->clientMock = $this->createMock(ClientInterface::class);

        $this->subject = new JobHttpClient('http://domain.tld/cronjob', $this->clientMock);

        $fixBaseUrlCallback = function (array $options) {
            Assert::assertEquals('http://domain.tld/cronjob/', $options['base_uri']);

            return true;
        };

        $this->clientMock->expects($this->once())
            ->method('request')
            ->with('post', 'job', $this->callback($fixBaseUrlCallback))
            ->willReturn($this->createMock(ResponseInterface::class));
        ;

        $this->subject->process('someId');
    }

    public function testList()
    {
        $response = $this->createMock(ResponseInterface::class);

        $expectedOptions = array_merge([
            'some' => 'value',
            'query' => ['filter' => 'value'],
        ], $this->defaultHeaders);

        $this->clientMock->expects($this->once())->method('request')->with('get', 'job', $expectedOptions)->willReturn($response);

        $this->assertSame($response, $this->subject->list(['filter' => 'value'], ['some' => 'value']));
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
