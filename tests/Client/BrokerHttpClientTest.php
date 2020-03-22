<?php

namespace Abc\Job\Tests\Client;

use Abc\Job\Client\BrokerHttpClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BrokerHttpClientTest extends TestCase
{
    /**
     * @var ClientInterface|MockObject
     */
    private $clientMock;

    /**
     * @var BrokerHttpClient
     */
    private $subject;

    private $defaultHeaders;

    public function setUp(): void
    {
        $this->clientMock = $this->createMock(ClientInterface::class);

        $this->subject = new BrokerHttpClient('http://domain.tld/cronjob/', $this->clientMock);

        $this->defaultHeaders = [
            'base_uri' => 'http://domain.tld/cronjob/',
            'http_errors' => false,
            'headers' => ['Content-Type' => 'application/json'],
        ];
    }

    public function testSetup()
    {
        $response = new Response(200, []);

        $expectedOptions = array_merge(
            [
                'some' => 'value',
            ],
            $this->defaultHeaders
        );

        $this->clientMock->expects($this->once())->method('request')->with(
            'post',
            'broker/someId/setup',
            $expectedOptions
        )->willReturn($response);

        $this->subject->setup('someId', ['some' => 'value']);
    }
}
