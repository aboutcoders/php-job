<?php

namespace Abc\Job\Tests\Client;

use Abc\ApiProblem\ApiProblemException;
use Abc\Job\Client\BrokerClient;
use Abc\Job\Client\BrokerHttpClient;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;

class BrokerClientTest extends AbstractClientTestCase
{
    /**
     * @var BrokerHttpClient|MockObject
     */
    private $httpClient;

    public function setUp(): void
    {
        $this->httpClient = $this->createMock(BrokerHttpClient::class);
    }

    public function testSetup()
    {
        $response = new Response(200, []);

        $this->httpClient->expects($this->once())->method('setup')->with('someName')->willReturn($response);

        (new BrokerClient($this->httpClient))->setup('someName');
    }

    public function testListWithHttpError()
    {
        $this->httpClient->expects($this->once())->method('setup')->with('someName')->willReturn(
            new Response(400, [], $this->createApiProblemJson())
        );

        $this->expectException(ApiProblemException::class);

        (new BrokerClient($this->httpClient))->setup('someName');
    }
}
