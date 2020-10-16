<?php

namespace Abc\Job\Tests\Broker;

use Abc\Job\Broker\Broker;
use Abc\Job\Broker\Route;
use Abc\Job\Broker\RouteCollection;
use Abc\Job\Interop\DriverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BrokerTest extends TestCase
{
    /**
     * @var DriverInterface|MockObject
     */
    private $driver;

    public function setUp(): void
    {
        $this->driver = $this->createMock(DriverInterface::class);
    }

    public function testGetName()
    {
        $this->assertSame(
            'someName',
            (new Broker('someName', $this->driver, new RouteCollection()))->getName()
        );
    }

    public function testSetup()
    {
        $routeA = new Route('jobA', 'queueA', 'replyToA');
        $routeB = new Route('jobB', 'queueB', 'replyToB');
        $routeC = new Route('jobC', 'queueA', 'replyToA');

        $routes = new RouteCollection([$routeA, $routeB, $routeC]);

        $this->driver->expects($this->exactly(4))->method('declareQueue');

        $this->driver->method('declareQueue')
            ->withConsecutive(
                ['queueA'],
                ['replyToA'],
                ['queueB'],
                ['replyToB']
            );

        (new Broker('someName', $this->driver, $routes))->setup();
    }

    public function testSetupNoRoutes()
    {
        $this->expectException(\LogicException::class);

        (new Broker('someName', $this->driver, new RouteCollection()))->setup();
    }
}
