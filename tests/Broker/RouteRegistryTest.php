<?php

namespace Abc\Job\Tests\Broker;

use Abc\Job\Broker\Route;
use Abc\Job\Broker\RouteCollection;
use PHPUnit\Framework\TestCase;

class RouteCollectionTest extends TestCase
{
    public function testAll()
    {
        $route_A = new Route('jobNameA', 'queueName', 'replyTo');
        $route_B = new Route('jobNameB', 'queueName', 'replyTo');

        $subject = new RouteCollection([$route_A]);
        $subject->add($route_B);

        $this->assertEquals([$route_A, $route_B], $subject->all());
    }

    public function testGet()
    {
        $route_A = new Route('jobNameA', 'queueName', 'replyTo');
        $route_B = new Route('jobNameB', 'queueName', 'replyTo');

        $subject = new RouteCollection([$route_A, $route_B]);

        $this->assertSame($route_A, $subject->get($route_A->getJobName()));
    }

    public function testArray()
    {
        $route_A = new Route('jobNameA', 'queueName', 'replyTo');
        $route_B = new Route('jobNameB', 'queueName', 'replyTo');

        $subject = new RouteCollection([$route_A, $route_B]);

        $this->assertEquals($subject, RouteCollection::fromArray($subject->toArray()));
    }
}
