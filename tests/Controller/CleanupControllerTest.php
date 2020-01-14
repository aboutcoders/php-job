<?php

namespace Abc\Job\Tests\Controller;

use Abc\Job\Controller\CleanupController;
use Abc\Job\Model\CronJobManagerInterface;
use Abc\Job\Model\JobManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;

class CleanupControllerTest extends AbstractControllerTestCase
{
    /**
     * @var JobManagerInterface|MockObject
     */
    private $jobManagerMock;

    /**
     * @var CronJobManagerInterface|MockObject
     */
    private $cronJobManagerMock;

    /**
     * @var CleanupController
     */
    private $subject;

    public function setUp(): void
    {
        $this->jobManagerMock = $this->createMock(JobManagerInterface::class);
        $this->cronJobManagerMock = $this->createMock(CronJobManagerInterface::class);
        $this->subject = new CleanupController($this->jobManagerMock, $this->cronJobManagerMock, new NullLogger());
    }

    public function testDeleteJobs()
    {
        $this->jobManagerMock->expects($this->once())->method('deleteAll')->willReturn(1);

        $response = $this->subject->deleteJobs('requestUri');

        $this->assertStatusCode(204, $response);
    }

    public function testDeleteCronJobs()
    {
        $this->cronJobManagerMock->expects($this->once())->method('deleteAll')->willReturn(1);

        $response = $this->subject->deleteCronJobs('requestUri');

        $this->assertStatusCode(204, $response);
    }
}
