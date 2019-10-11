<?php

namespace Abc\Job\Tests;

use Abc\ApiProblem\InvalidParameter;
use Abc\Job\Broker\Route;
use Abc\Job\Broker\RouteRegistryInterface;
use Abc\Job\HttpRouteServer;
use Abc\Job\ValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;

class HttpRouteServerTest extends HttpServerTestCase
{
    /**
     * @var RouteRegistryInterface|MockObject
     */
    private $routeRegistry;

    /**
     * @var ValidatorInterface|MockObject
     */
    private $validatorMock;

    /**
     * @var HttpRouteServer
     */
    private $subject;

    public function setUp(): void
    {
        $this->routeRegistry = $this->createMock(RouteRegistryInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->subject = new HttpRouteServer($this->routeRegistry, $this->validatorMock, new NullLogger());
    }

    public function testAllSuccess()
    {
        $route = new Route('jobName', 'queueName', 'replyTo');

        $this->routeRegistry->expects($this->once())->method('all')->with()->willReturn([$route]);

        $response = $this->subject->all('requestUri');

        $this->assertStatusCode(200, $response);
        $this->assertStdJsonResponseHeader($response);

        $this->assertEquals('[{"jobName":"jobName","queueName":"queueName","replyTo":"replyTo"}]', $response->getBody()->getContents());
    }

    public function testAllServerError()
    {
        $this->routeRegistry->expects($this->once())->method('all')->with()->willThrowException(new \Exception('someExceptionMessage'));

        $response = $this->subject->all('requestUri');

        $this->assertServerErrorResponse($response);
    }

    public function testCreateSuccessWithSingleRoute()
    {
        $route = new Route('jobName', 'queueName', 'replyTo');
        $json = $route->toJson();

        $this->validatorMock->expects($this->once())->method('validate')->with($json)->willReturn([]);

        $this->routeRegistry->expects($this->once())->method('add')->with($this->equalTo($route));

        $response = $this->subject->create($json, 'requestUri');

        $this->assertStatusCode(204, $response);
    }

    public function testCreateSuccessWithRouteArray()
    {
        $route = new Route('jobName', 'queueName', 'replyTo');
        $json = json_encode([(object) $route->toArray()]);

        $this->validatorMock->expects($this->once())->method('validate')->with($json)->willReturn([]);

        $this->routeRegistry->expects($this->once())->method('add')->with($this->equalTo($route));

        $response = $this->subject->create($json, 'requestUri');

        $this->assertStatusCode(204, $response);
    }

    public function testCreateValidatorError()
    {
        $invalidParam = new InvalidParameter('name', 'reason', 'value');

        $route = new Route('jobName', 'queueName', 'replyTo');
        $json = $route->toJson();

        $this->validatorMock->expects($this->once())->method('validate')->with($json, Route::class)->willReturn([$invalidParam]);

        $this->routeRegistry->expects($this->never())->method('add');

        $response = $this->subject->create($json, 'requestUri');

        $this->assertInvalidParameterResponse($response);
    }

    public function testCreateServerError()
    {
        $this->validatorMock->expects($this->once())->method('validate')->willThrowException(new \Exception('someExceptionMessage'));

        $response = $this->subject->create('someJson', 'requestUri');

        $this->assertServerErrorResponse($response);
    }
}
