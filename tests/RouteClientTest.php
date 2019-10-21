<?php

namespace Abc\Job\Tests;

use Abc\ApiProblem\ApiProblemException;
use Abc\Job\Broker\Route;
use Abc\Job\HttpRouteClient;
use Abc\Job\RouteClient;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;

class RouteClientTest extends ClientTestCase
{
    /**
     * @var HttpRouteClient|MockObject
     */
    private $httpClientMock;

    /**
     * @var RouteClient
     */
    private $subject;

    public function setUp(): void
    {
        $this->httpClientMock = $this->createMock(HttpRouteClient::class);

        $this->subject = new RouteClient($this->httpClientMock, new NullLogger());
    }

    public function testAll()
    {
        $route = new Route('jobName', 'queueName', 'replyTo');

        $json = json_encode([(object) $route->toArray()]);

        $response = new Response(200, [], $json);

        $this->httpClientMock->expects($this->once())->method('all')->with()->willReturn($response);

        $this->assertEquals([$route], $this->subject->all());
    }

    public function testAllWithHttpError()
    {
        $this->httpClientMock->expects($this->once())->method('all')->with()->willReturn(new Response(400, [], $this->createApiProblemJson()));

        $this->expectException(ApiProblemException::class);

        $this->subject->all();
    }

    public function testAdd()
    {
        $route = new Route('jobName', 'queueName', 'replyTo');

        $json = json_encode([(object) $route->toArray()]);

        $response = new Response(204, [], $json);

        $this->httpClientMock->expects($this->once())->method('add')->with($json)->willReturn($response);

        $this->subject->add([$route]);
    }

    public function testAddWithApiException()
    {
        $route = new Route('jobName', 'queueName', 'replyTo');

        $json = json_encode([(object) $route->toArray()]);

        $this->httpClientMock->expects($this->once())->method('add')->with($json)->willReturn(new Response(400, [], $this->createApiProblemJson()));

        $this->expectException(ApiProblemException::class);

        $this->subject->add([$route]);
    }
}
