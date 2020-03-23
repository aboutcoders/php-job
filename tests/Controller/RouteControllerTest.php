<?php

namespace Abc\Job\Tests\Controller;

use Abc\ApiProblem\InvalidParameter;
use Abc\Job\Broker\Route;
use Abc\Job\Broker\RouteRegistryInterface;
use Abc\Job\Controller\RouteController;
use Abc\Job\InvalidJsonException;
use Abc\Job\ValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;

class RouteControllerTest extends AbstractControllerTestCase
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
     * @var RouteController
     */
    private $subject;

    public function setUp(): void
    {
        $this->routeRegistry = $this->createMock(RouteRegistryInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->subject = new RouteController($this->routeRegistry, $this->validatorMock, new NullLogger());
    }

    public function testAll()
    {
        $route = new Route('jobName', 'queueName', 'replyTo');

        $this->routeRegistry->expects($this->once())->method('all')->with()->willReturn([$route]);

        $response = $this->subject->list('requestUri');

        $this->assertStatusCode(200, $response);
        $this->assertStdJsonResponseHeader($response);

        $this->assertEquals('[{"name":"jobName","queue":"queueName","replyTo":"replyTo"}]', $response->getBody()->getContents());
    }

    public function testAllWithServerException()
    {
        $this->routeRegistry->expects($this->once())->method('all')->with()->willThrowException(new \Exception('someExceptionMessage'));

        $response = $this->subject->list('requestUri');

        $this->assertServerErrorResponse($response);
    }

    public function testCreateWithRoute()
    {
        $route = new Route('jobName', 'queueName', 'replyTo');
        $json = $route->toJson();

        $this->validatorMock->expects($this->once())->method('validate')->with($json)->willReturn([]);

        $this->routeRegistry->expects($this->once())->method('add')->with($this->equalTo($route));

        $response = $this->subject->set($json, 'requestUri');

        $this->assertStatusCode(201, $response);
    }

    public function testCreateWithRoutes()
    {
        $route = new Route('jobName', 'queueName', 'replyTo');
        $json = json_encode([(object) $route->toArray()]);

        $this->validatorMock->expects($this->once())->method('validate')->with($json)->willReturn([]);

        $this->routeRegistry->expects($this->once())->method('add')->with($this->equalTo($route));

        $response = $this->subject->set($json, 'requestUri');

        $this->assertStatusCode(201, $response);
    }

    public function testCreateWithValidatorError()
    {
        $invalidParam = new InvalidParameter('name', 'reason', 'value');

        $route = new Route('jobName', 'queueName', 'replyTo');
        $json = $route->toJson();

        $this->validatorMock->expects($this->once())->method('validate')->with($json, Route::class)->willReturn([$invalidParam]);

        $this->routeRegistry->expects($this->never())->method('add');

        $response = $this->subject->set($json, 'requestUri');

        $this->assertInvalidParameterResponse($response);
    }

    public function testCreateWithInvalidJson()
    {
        $route = new Route('jobName', 'queueName', 'replyTo');
        $json = $route->toJson();

        $this->validatorMock->expects($this->once())->method('validate')->with($json, Route::class)->willThrowException(new InvalidJsonException('some error'));

        $this->routeRegistry->expects($this->never())->method('add');

        $response = $this->subject->set($json, 'requestUri');

        $this->assertInvalidJsonResponse($response, 'some error');
    }

    public function testCreateWithServerException()
    {
        $this->validatorMock->expects($this->once())->method('validate')->willThrowException(new \Exception('someExceptionMessage'));

        $response = $this->subject->set('someJson', 'requestUri');

        $this->assertServerErrorResponse($response);
    }
}
