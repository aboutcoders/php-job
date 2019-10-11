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

        $this->assertEquals($data['jobName'], $route->getJobName());
        $this->assertEquals($data['queueName'], $route->getQueueName());
        $this->assertEquals($data['replyTo'], $route->getReplyTo());

        $this->assertEquals($route, Route::fromArray($data));
    }
}
