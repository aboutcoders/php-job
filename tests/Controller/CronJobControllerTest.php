<?php

namespace Abc\Job\Tests\Controller;

use Abc\ApiProblem\InvalidParameter;
use Abc\Job\Controller\CronJobController;
use Abc\Job\CronJobFilter;
use Abc\Job\CronJobManager;
use Abc\Job\InvalidJsonException;
use Abc\Job\Job;
use Abc\Job\Model\CronJob;
use Abc\Job\Model\CronJobInterface;
use Abc\Job\Type;
use Abc\Job\ValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;

class CronJobControllerTest extends AbstractControllerTestCase
{
    /**
     * @var CronJobManager|MockObject
     */
    private $cronJobManagerMock;

    /**
     * @var ValidatorInterface|MockObject
     */
    private $validatorMock;

    /**
     * @var CronJobController
     */
    private $subject;

    public function setUp(): void
    {
        $this->cronJobManagerMock = $this->createMock(CronJobManager::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->subject = new CronJobController($this->cronJobManagerMock, $this->validatorMock, new NullLogger());
    }

    public function testList()
    {
        $queryString = 'foo=bar';

        $cronJob = $this->createManagedCronJob();

        $this->validatorMock->expects($this->once())->method('validate')->with('{"foo":"bar"}', CronJobFilter::class)->willReturn([]);

        $this->cronJobManagerMock->expects($this->once())->method('list')->willReturn([$cronJob]);

        $response = $this->subject->list($queryString, 'requestUri');

        $this->assertStatusCode(200, $response);
        $this->assertStdJsonResponseHeader($response);

        $json = $response->getBody()->getContents();

        $data = json_decode($json, true);

        $this->assertEquals(1, count($data));
        $this->assertJsonManagedCronJob($cronJob, $data[0]);
    }

    public function testListWithValidatorError()
    {
        $invalidParam = new InvalidParameter('name', 'reason', 'value');
        $queryString = 'foo=bar';

        $this->validatorMock->expects($this->once())->method('validate')->with('{"foo":"bar"}', CronJobFilter::class)->willReturn([$invalidParam]);

        $this->cronJobManagerMock->expects($this->never())->method('list');

        $response = $this->subject->list($queryString, 'requestUri');

        $this->assertInvalidParameterResponse($response);
    }

    public function testListWithServerException()
    {
        $queryString = 'foo=bar';

        $this->validatorMock->expects($this->once())->method('validate')->with('{"foo":"bar"}', CronJobFilter::class)->willReturn([]);

        $this->cronJobManagerMock->expects($this->once())->method('list')->willThrowException(new \Exception());

        $response = $this->subject->list($queryString, 'requestUri');

        $this->assertServerErrorResponse($response);
    }

    public function testFind()
    {
        $cronJob = $this->createManagedCronJob();

        $this->cronJobManagerMock->expects($this->once())->method('find')->with('cronJobId')->willReturn($cronJob);

        $response = $this->subject->find('cronJobId', 'requestUri');

        $this->assertStatusCode(200, $response);
        $this->assertStdJsonResponseHeader($response);

        $json = $response->getBody()->getContents();

        $data = json_decode($json, true);

        $this->assertJsonManagedCronJob($cronJob, $data);
    }

    public function testFindWithCronJobNotFound()
    {
        $this->cronJobManagerMock->expects($this->once())->method('find')->with('cronJobId')->willReturn(null);

        $response = $this->subject->find('cronJobId', 'requestUri');

        $this->assertStatusCode(404, $response);
        $this->assertNotFoundResponse($response, 'cronJobId');
    }

    public function testFindWithServerException()
    {
        $this->cronJobManagerMock->expects($this->once())->method('find')->willThrowException(new \Exception());

        $response = $this->subject->find('cronJobId', 'requestUri');

        $this->assertServerErrorResponse($response);
    }

    public function testCreate()
    {
        $cronJob = static::createCronJob();
        $managedCronJob = static::createManagedCronJob();
        $json = $cronJob->toJson();

        $this->validatorMock->expects($this->once())->method('validate')->with($json, \Abc\Job\CronJob::class)->willReturn([]);

        $this->cronJobManagerMock->expects($this->once())->method('create')->with($cronJob->getSchedule(), $this->equalTo($cronJob->getJob()))->willReturn($managedCronJob);

        $response = $this->subject->create($json, 'requestUri');

        $this->assertStatusCode(201, $response);
        $this->assertStdJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertJsonManagedCronJob($managedCronJob, $data);
    }

    public function testCreateWithValidatorError()
    {
        $invalidParam = new InvalidParameter('name', 'reason', 'value');
        $cronJob = static::createCronJob();
        $json = $cronJob->toJson();

        $this->validatorMock->expects($this->once())->method('validate')->with($json, \Abc\Job\CronJob::class)->willReturn([$invalidParam]);

        $this->cronJobManagerMock->expects($this->never())->method('create');

        $response = $this->subject->create($json, 'requestUri');

        $this->assertInvalidParameterResponse($response);
    }

    public function testCreateWithInvalidJson()
    {
        $json = 'someJson';

        $this->validatorMock->expects($this->once())->method('validate')->with($json, \Abc\Job\CronJob::class)->willThrowException(new InvalidJsonException('some message'));

        $this->cronJobManagerMock->expects($this->never())->method('create');

        $response = $this->subject->create($json, 'requestUri');

        $this->assertInvalidJsonResponse($response, 'some message');
    }

    public function testCreateWithServerException()
    {
        $cronJob = static::createCronJob();
        $json = $cronJob->toJson();

        $this->validatorMock->expects($this->once())->method('validate')->with($json, \Abc\Job\CronJob::class)->willReturn([]);

        $this->cronJobManagerMock->expects($this->once())->method('create')->willThrowException(new \Exception());

        $response = $this->subject->create($json, 'requestUri');

        $this->assertServerErrorResponse($response);
    }

    public function testUpdate()
    {
        $managedCronJob = static::createManagedCronJob();

        $updatedJob = $cronJob = new CronJob('updatedSchedule', new Job(Type::JOB(), 'anotherJobName'));
        $json = $updatedJob->toJson();
        $expectedJob = clone $managedCronJob;
        $expectedJob->setJob($updatedJob->getJob());
        $expectedJob->setSchedule($updatedJob->getSchedule());

        $this->validatorMock->expects($this->once())->method('validate')->with($json, \Abc\Job\CronJob::class)->willReturn([]);

        $this->cronJobManagerMock->expects($this->once())->method('find')->with('cronJobId')->willReturn($managedCronJob);
        $this->cronJobManagerMock->expects($this->once())->method('update')->with($expectedJob);

        $response = $this->subject->update('cronJobId', $json, 'requestUri');

        $this->assertStatusCode(201, $response);
        $this->assertStdJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertJsonManagedCronJob($expectedJob, $data);
    }

    public function testUpdateCronJobNotFound()
    {
        $updatedJob = $cronJob = new CronJob('updatedSchedule', new Job(Type::JOB(), 'anotherJobName'));
        $json = $updatedJob->toJson();

        $this->validatorMock->expects($this->once())->method('validate')->with($json, \Abc\Job\CronJob::class)->willReturn([]);

        $this->cronJobManagerMock->expects($this->once())->method('find')->with('cronJobId')->willReturn(null);
        $this->cronJobManagerMock->expects($this->never())->method('update');

        $response = $this->subject->update('cronJobId', $json, 'requestUri');

        $this->assertStatusCode(404, $response);
        $this->assertNotFoundResponse($response, 'cronJobId');
    }

    public function testUpdateWithValidatorError()
    {
        $invalidParam = new InvalidParameter('name', 'reason', 'value');
        $cronJob = static::createCronJob();
        $json = $cronJob->toJson();

        $this->validatorMock->expects($this->once())->method('validate')->with($json, \Abc\Job\CronJob::class)->willReturn([$invalidParam]);

        $this->cronJobManagerMock->expects($this->never())->method('find');

        $response = $this->subject->update('someCronJobId', $json, 'requestUri');

        $this->assertInvalidParameterResponse($response);
    }

    public function testUpdateWithInvalidJson()
    {
        $json = 'someJson';

        $this->validatorMock->expects($this->once())->method('validate')->with($json, \Abc\Job\CronJob::class)->willThrowException(new InvalidJsonException('some message'));

        $this->cronJobManagerMock->expects($this->never())->method('create');

        $response = $this->subject->update('someCronJobId', $json, 'requestUri');

        $this->assertInvalidJsonResponse($response, 'some message');
    }

    public function testUpdateWithServerException()
    {
        $cronJob = static::createCronJob();
        $json = $cronJob->toJson();

        $this->validatorMock->expects($this->once())->method('validate')->with($json, \Abc\Job\CronJob::class)->willReturn([]);

        $this->cronJobManagerMock->expects($this->once())->method('find')->willThrowException(new \Exception());

        $response = $this->subject->update('someCronJobId', $json, 'requestUri');

        $this->assertServerErrorResponse($response);
    }

    public function testDelete()
    {
        $managedCronJob = $this->createManagedCronJob();

        $this->cronJobManagerMock->expects($this->once())->method('find')->with('cronJobId')->willReturn($managedCronJob);
        $this->cronJobManagerMock->expects($this->once())->method('delete')->with($managedCronJob);

        $response = $this->subject->delete('cronJobId', 'requestUri');

        $this->assertStatusCode(204, $response);
        $this->assertStdJsonResponseHeader($response);
    }

    public function testDeleteWithCronJobNotFound()
    {
        $this->cronJobManagerMock->expects($this->once())->method('find')->with('cronJobId')->willReturn(null);
        $this->cronJobManagerMock->expects($this->never())->method('delete');

        $response = $this->subject->delete('cronJobId', 'requestUri');

        $this->assertStatusCode(404, $response);
        $this->assertNotFoundResponse($response, 'cronJobId');
    }

    public function testDeleteWithServerException()
    {
        $this->cronJobManagerMock->expects($this->once())->method('find')->willThrowException(new \Exception());

        $response = $this->subject->delete('cronJobId', 'requestUri');

        $this->assertServerErrorResponse($response);
    }

    private function createCronJob()
    {
        $job = new Job(Type::JOB(), 'jobName', 'input', [], false, 'externalId');

        $cronJob = new CronJob('* * * * *', $job);

        return $cronJob;
    }

    private function createManagedCronJob(\Abc\Job\CronJob $cronJob = null): CronJobInterface
    {
        if (null === $cronJob) {
            $cronJob = static::createCronJob();
        }

        $cronJob->setId('someId');
        $cronJob->setUpdatedAt(new \DateTime("@100"));
        $cronJob->setCreatedAt(new \DateTime("@1000"));

        return $cronJob;
    }

    private function assertJsonManagedCronJob(CronJobInterface $managedCronJob, array $data): void
    {
        $this->assertEquals($data['id'], $managedCronJob->getId());
        $this->assertEquals($data['schedule'], $managedCronJob->getSchedule());
        $this->assertEquals($data['name'], $managedCronJob->getJob()->getName());
        $this->assertEquals($data['input'], $managedCronJob->getJob()->getInput());
        $this->assertEquals($data['updated'], '1970-01-01T00:01:40+00:00');
        $this->assertEquals($data['created'], '1970-01-01T00:16:40+00:00');
    }
}
