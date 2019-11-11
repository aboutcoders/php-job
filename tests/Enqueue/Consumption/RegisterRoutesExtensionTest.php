<?php

namespace Abc\Job\Tests\Enqueue\Consumption;

use Abc\Job\Broker\Route;
use Abc\Job\Broker\RouteCollection;
use Abc\Job\Client\RouteClient;
use Abc\Job\Enqueue\Consumption\RegisterRoutesExtension;
use Enqueue\Consumption\Context\Start;
use Interop\Queue\Context;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class RegisterRoutesExtensionTest extends TestCase
{
    /**
     * @var RouteClient
     */
    private $routeClientMock;

    /**
     * @var RouteCollection
     */
    private $routeCollectionMock;

    /**
     * @var RegisterRoutesExtension
     */
    private $subject;

    public function setUp(): void
    {
        $this->routeClientMock = $this->createMock(RouteClient::class);
        $this->routeCollectionMock = $this->createMock(RouteCollection::class);
        $this->subject = new RegisterRoutesExtension($this->routeClientMock, $this->routeCollectionMock);
    }

    public function testOnStart()
    {
        $context = new Start($this->createMock(Context::class), new NullLogger(), [], 100, 100);
        $route = $this->createMock(Route::class);

        $this->routeCollectionMock->expects($this->once())->method('all')->willReturn([$route]);
        $this->routeClientMock->expects($this->once())->method('add')->with([$route]);

        $this->subject->onStart($context);
    }
}
