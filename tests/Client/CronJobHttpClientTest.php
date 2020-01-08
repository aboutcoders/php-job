<?php

namespace Abc\Job\Tests\Client;

use Abc\Job\Client\CronJobHttpClient;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class CronJobHttpClientTest extends TestCase
{
    /**
     * @var ClientInterface|MockObject
     */
    private $clientMock;

    /**
     * @var CronJobHttpClient
     */
    private $subject;

    private $defaultHeaders;

    public function setUp(): void
    {
        $this->clientMock = $this->createMock(ClientInterface::class);

        $this->subject = new CronJobHttpClient('http://domain.tld/cronjob/', $this->clientMock);

        $this->defaultHeaders = [
            'base_uri' => 'http://domain.tld/cronjob',
            'http_errors' => false,
            'headers' => ['Content-Type' => 'application/json'],
        ];
    }

    public function testList()
    {
        $response = $this->createMock(ResponseInterface::class);

        $expectedOptions = array_merge([
            'some' => 'value',
            'query' => ['filter' => 'value'],
        ], $this->defaultHeaders);

        $this->clientMock->expects($this->once())->method('request')->with('get', 'cronjob', $expectedOptions)->willReturn($response);

        $this->assertSame($response, $this->subject->list(['filter' => 'value'], ['some' => 'value']));
    }

    public function testFind()
    {
        $response = $this->createMock(ResponseInterface::class);

        $expectedOptions = array_merge([
            'some' => 'value',
        ], $this->defaultHeaders);

        $this->clientMock->expects($this->once())->method('request')->with('get', 'cronjob/someId', $expectedOptions)->willReturn($response);

        $this->assertSame($response, $this->subject->find('someId', ['some' => 'value']));
    }

    public function testCreate()
    {
        $response = $this->createMock(ResponseInterface::class);

        $expectedOptions = array_merge([
            'some' => 'value',
            'body' => 'someJson',
        ], $this->defaultHeaders);

        $this->clientMock->expects($this->once())->method('request')->with('post', 'cronjob', $expectedOptions)->willReturn($response);

        $this->assertSame($response, $this->subject->create('someJson', ['some' => 'value']));
    }

    public function testUpdate()
    {
        $response = $this->createMock(ResponseInterface::class);

        $expectedOptions = array_merge([
            'some' => 'value',
            'body' => 'someJson',
        ], $this->defaultHeaders);

        $this->clientMock->expects($this->once())->method('request')->with('put', 'cronjob/someId', $expectedOptions)->willReturn($response);

        $this->assertSame($response, $this->subject->update('someId', 'someJson', ['some' => 'value']));
    }

    public function testDelete()
    {
        $response = $this->createMock(ResponseInterface::class);

        $expectedOptions = array_merge([
            'some' => 'value',
        ], $this->defaultHeaders);

        $this->clientMock->expects($this->once())->method('request')->with('delete', 'cronjob/someId', $expectedOptions)->willReturn($response);

        $this->assertSame($response, $this->subject->delete('someId', ['some' => 'value']));
    }
}
