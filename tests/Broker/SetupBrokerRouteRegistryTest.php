<?php

namespace Abc\Job\Tests\Symfony\Broker;

use Abc\Job\Broker\Route;
use Abc\Job\Broker\RouteRegistryInterface;
use Abc\Job\Broker\SetupBrokerRouteRegistry;
use Abc\Job\Interop\DriverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetupBrokerRouteRegistryTest extends TestCase
{
    /**
     * @var DriverInterface|MockObject
     */
    private $driverMock;

    /**
     * @var RouteRegistryInterface|MockObject
     */
    private $registryMock;

    /**
     * @var SetupBrokerRouteRegistry
     */
    private $subject;

    public function setUp(): void
    {
        $this->driverMock = $this->createMock(DriverInterface::class);
        $this->registryMock = $this->createMock(RouteRegistryInterface::class);
        $this->subject = new SetupBrokerRouteRegistry($this->driverMock, $this->registryMock);
    }

    public function testAll()
    {
        $route = $this->createMock(Route::class);

        $this->registryMock->expects($this->once())->method('all')->willReturn([$route]);

        $this->assertSame([$route], $this->subject->all());
    }

    public function testGet()
    {
        $jobName = 'someJob';

        $route = $this->createMock(Route::class);

        $this->registryMock->expects($this->once())->method('get')->with($jobName)->willReturn($route);

        $this->assertSame($route, $this->subject->get($jobName));
    }

    public function testAdd()
    {
        $route = new Route('jobName', 'queueName', 'replyToName');

        $this->registryMock->expects($this->once())->method('add')->with($route);

        $this->driverMock->expects($this->at(0))->method('declareQueue')->with($route->getQueue());
        $this->driverMock->expects($this->at(1))->method('declareQueue')->with($route->getReplyTo());

        $this->subject->add($route);
    }
}
