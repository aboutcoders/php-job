<?php

namespace Abc\Job\Tests\Client;

use Abc\ApiProblem\ApiProblemException;
use Abc\Job\Client\BrokerClient;
use Abc\Job\Client\BrokerHttpClient;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;

class BoundBrokerClientTest extends AbstractClientTestCase
{
    /**
     * @var BrokerHttpClient|MockObject
     */
    private $httpClientMock;

    /**
     * @var BrokerClient
     */
    private $subject;

    public function setUp(): void
    {
        $this->httpClientMock = $this->createMock(BrokerHttpClient::class);

        $this->subject = new BrokerClient($this->httpClientMock);
    }

    public function testSetup()
    {
        $response = new Response(200, []);

        $this->httpClientMock->expects($this->once())->method('setup')->with('someName')->willReturn($response);

        $this->subject->setup('someName');
    }

    public function testListWithHttpError()
    {
        $this->httpClientMock->expects($this->once())->method('setup')->with('someName')->willReturn(
            new Response(400, [], $this->createApiProblemJson())
        );

        $this->expectException(ApiProblemException::class);

        $this->subject->setup('someName');
    }
}
