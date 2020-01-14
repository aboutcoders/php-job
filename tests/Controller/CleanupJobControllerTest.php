<?php

namespace Abc\Job\Tests\Controller;

use Abc\Job\Controller\CleanupJobController;
use Abc\Job\Model\CronJobManagerInterface;
use Abc\Job\Model\JobManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;

class CleanupJobControllerTest extends AbstractControllerTestCase
{
    /**
     * @var JobManagerInterface|MockObject
     */
    private $jobManagerMock;


    /**
     * @var CleanupJobController
     */
    private $subject;

    public function setUp(): void
    {
        $this->jobManagerMock = $this->createMock(JobManagerInterface::class);
        $this->subject = new CleanupJobController($this->jobManagerMock, new NullLogger());
    }

    public function testDeleteJobs()
    {
        $this->jobManagerMock->expects($this->once())->method('deleteAll')->willReturn(1);

        $response = $this->subject->execute('requestUri');

        $this->assertStatusCode(204, $response);
    }
}
