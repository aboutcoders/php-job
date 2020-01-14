<?php

namespace Abc\Job\Tests\Symfony\Command;

use Abc\Job\Broker\Route;
use Abc\Job\Broker\RouteCollection;
use Abc\Job\Client\RouteClient;
use Abc\Job\Symfony\Command\RegisterRoutesCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class RegisterRoutesCommandTest extends TestCase
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
     * @var RegisterRoutesCommand
     */
    private $subject;

    public function setUp(): void
    {
        $this->routeClientMock = $this->createMock(RouteClient::class);
        $this->routeCollectionMock = $this->createMock(RouteCollection::class);
        $this->subject = new RegisterRoutesCommand($this->routeClientMock, $this->routeCollectionMock);
    }

    public function testOnStart()
    {
        $route = $this->createMock(Route::class);

        $this->routeCollectionMock->expects($this->once())->method('all')->willReturn([$route]);
        $this->routeClientMock->expects($this->once())->method('add')->with([$route]);

        $exitCode = $this->subject->run(new ArrayInput([]), new NullOutput());
        $this->assertSame(0, $exitCode);
    }
}
