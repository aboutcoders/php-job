<?php

namespace Abc\Job\Tests\Controller;

use Abc\Job\Controller\CleanupCronJobController;
use Abc\Job\Controller\CleanupJobController;
use Abc\Job\CronJobManager;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;

class CleanupCronJobControllerTest extends AbstractControllerTestCase
{
    /**
     * @var CronJobManager|MockObject
     */
    private $cronJobManagerMock;

    /**
     * @var CleanupJobController
     */
    private $subject;

    public function setUp(): void
    {
        $this->cronJobManagerMock = $this->createMock(CronJobManager::class);
        $this->subject = new CleanupCronJobController($this->cronJobManagerMock, new NullLogger());
    }

    public function testDeleteCronJobs()
    {
        $this->cronJobManagerMock->expects($this->once())->method('deleteAll')->willReturn(1);

        $response = $this->subject->execute('requestUri');

        $this->assertStatusCode(204, $response);
    }
}
