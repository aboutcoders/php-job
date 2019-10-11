<?php

namespace Abc\Job\Tests\Broker;

use Abc\Job\Broker\Route;
use Abc\Job\Broker\ManagedRouteRegistry;
use Abc\Job\Model\RouteManagerInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ManagedRouteRegistryTest extends TestCase
{
    /**
     * @var RouteManagerInterface|MockObject
     */
    private $routeManagerMock;

    /**
     * @var ManagedRouteRegistry
     */
    private $subject;

    public function setUp(): void
    {
        $this->routeManagerMock = $this->createMock(RouteManagerInterface::class);
        $this->subject = new ManagedRouteRegistry($this->routeManagerMock, new NullLogger());
    }

    public function testAll()
    {
        $route_A = new Route('jobNameA', 'queueA', 'replyToA');
        $route_B = new Route('jobNameB', 'queueB', 'replyToB');

        $this->routeManagerMock->expects($this->once())->method('all')->willReturn([$route_A, $route_B]);

        $this->assertEquals([$route_A, $route_B], $this->subject->all());
    }

    public function testGet()
    {
        $route = new Route('jobName', 'queueName', 'replyTo');

        $this->routeManagerMock->expects($this->once())->method('find')->with('jobName')->willReturn($route);

        $this->assertSame($route, $this->subject->get($route->getJobName()));
    }

    public function testAddWithNewRoute()
    {
        $route = new Route('jobName', 'queueName', 'replyTo');

        $this->routeManagerMock->expects($this->once())->method('find')->with('jobName')->willReturn(null);
        $this->routeManagerMock->expects($this->once())->method('save')->with($route);

        $this->subject->add($route);
    }

    public function testAddWithExistingRoute()
    {
        $route = new Route('jobName', 'updatedQueueName', 'updatedReplyTo');
        $existingRoute = new Route('jobName', 'queueName', 'replyTo');

        $this->routeManagerMock->expects($this->once())->method('find')->with('jobName')->willReturn($existingRoute);

        $assertUpdatedCallback = function (Route $route) use ($existingRoute) {
            Assert::assertSame($route, $existingRoute);
            Assert::assertEquals('updatedQueueName', $route->getQueueName());
            Assert::assertEquals('updatedReplyTo', $route->getReplyTo());

            return true;
        };

        $this->routeManagerMock->expects($this->once())->method('save')->with($this->callback($assertUpdatedCallback));

        $this->subject->add($route);
    }
}
