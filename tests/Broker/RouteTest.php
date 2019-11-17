<?php

namespace Abc\Job\Tests\Broker;

use Abc\Job\Broker\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function test()
    {
        $route = new Route('jobName', 'queueName', 'replyTo');

        $data = $route->toArray();

        $this->assertEquals($data['name'], $route->getName());
        $this->assertEquals($data['queue'], $route->getQueue());
        $this->assertEquals($data['replyTo'], $route->getReplyTo());

        $this->assertEquals($route, Route::fromArray($data));
    }
}
