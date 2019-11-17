<?php

namespace Abc\Job\Tests\Controller;

use Abc\ApiProblem\InvalidParameter;
use Abc\Job\Controller\JobController;
use Abc\Job\InvalidJsonException;
use Abc\Job\JobFilter;
use Abc\Job\Model\Job;
use Abc\Job\Model\JobInterface;
use Abc\Job\Result;
use Abc\Job\JobServerInterface;
use Abc\Job\Status;
use Abc\Job\Type;
use Abc\Job\ValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;

class JobControllerTest extends AbstractControllerTestCase
{
    /**
     * @var JobServerInterface|MockObject
     */
    private $serverMock;

    /**
     * @var ValidatorInterface|MockObject
     */
    private $validatorMock;

    /**
     * @var JobController
     */
    private $subject;

    public function setUp(): void
    {
        $this->serverMock = $this->createMock(JobServerInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->subject = new JobController($this->serverMock, $this->validatorMock, new NullLogger());
    }

    public function testList()
    {
        $queryString = 'foo=bar';

        $result = static::buildResult();

        $this->validatorMock->expects($this->once())->method('validate')->with('{"foo":"bar"}', JobFilter::class)->willReturn([]);

        $this->serverMock->expects($this->once())->method('list')->willReturn([$result]);

        $response = $this->subject->list($queryString, 'requestUri');

        $this->assertStatusCode(200, $response);
        $this->assertStdJsonResponseHeader($response);

        $json = $response->getBody()->getContents();
        $data = json_decode($json, true);

        $this->assertEquals(1, count($data));
        $this->assertJsonResult($result, $data[0]);
    }

    public function testListWithValidatorError()
    {
        $invalidParam = new InvalidParameter('name', 'reason', 'value');
        $queryString = 'foo=bar';

        $this->validatorMock->expects($this->once())->method('validate')->with('{"foo":"bar"}', JobFilter::class)->willReturn([$invalidParam]);

        $this->serverMock->expects($this->never())->method('list');

        $response = $this->subject->list($queryString, 'requestUri');

        $this->assertInvalidParameterResponse($response);
    }

    public function testListWithServerException()
    {
        $queryString = 'foo=bar';

        $this->validatorMock->expects($this->once())->method('validate')->with('{"foo":"bar"}', JobFilter::class)->willReturn([]);

        $this->serverMock->expects($this->once())->method('list')->willThrowException(new \Exception());

        $response = $this->subject->list($queryString, 'requestUri');

        $this->assertServerErrorResponse($response);
    }

    public function testProcess()
    {
        $result = static::buildResult();
        $job = new \Abc\Job\Job(Type::JOB(), 'jobName', 'input', [], true, 'externalId');
        $json = $job->toJson();

        $this->validatorMock->expects($this->once())->method('validate')->with($json, \Abc\Job\Job::class)->willReturn([]);

        $this->serverMock->expects($this->once())->method('process')->with($this->equalTo($job))->willReturn($result);

        $response = $this->subject->process($json, 'requestUri');

        $this->assertStatusCode(201, $response);
        $this->assertStdJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertJsonResult($result, $data);
    }

    public function testProcessWithValidatorError()
    {
        $invalidParam = new InvalidParameter('name', 'reason', 'value');
        $job = new \Abc\Job\Job(Type::JOB(), 'jobName', 'input', [], true, 'externalId');
        $json = $job->toJson();

        $this->validatorMock->expects($this->once())->method('validate')->with($json, \Abc\Job\Job::class)->willReturn([$invalidParam]);

        $this->serverMock->expects($this->never())->method('process');

        $response = $this->subject->process($json, 'requestUri');

        $this->assertInvalidParameterResponse($response);
    }

    public function testProcessWithInvalidJson()
    {
        $job = new \Abc\Job\Job(Type::JOB(), 'jobName', 'input', [], true, 'externalId');
        $json = $job->toJson();

        $this->validatorMock->expects($this->once())->method('validate')->with($json, \Abc\Job\Job::class)->willThrowException(new InvalidJsonException('some error'));

        $this->serverMock->expects($this->never())->method('process');

        $response = $this->subject->process($json, 'requestUri');

        $this->assertInvalidJsonResponse($response, 'some error');
    }

    public function testProcessWithServerException()
    {
        $job = new \Abc\Job\Job(Type::JOB(), 'jobName', 'input', [], true, 'externalId');
        $json = $job->toJson();

        $this->validatorMock->expects($this->once())->method('validate')->with($json, \Abc\Job\Job::class)->willReturn([]);

        $this->serverMock->expects($this->once())->method('process')->willThrowException(new \Exception());

        $response = $this->subject->process($json, 'requestUri');

        $this->assertServerErrorResponse($response);
    }

    public function testRestart()
    {
        $result = static::buildResult();

        $this->serverMock->expects($this->once())->method('restart')->with('jobId')->willReturn($result);

        $response = $this->subject->restart('jobId', 'requestUri');

        $this->assertStatusCode(200, $response);
        $this->assertStdJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertJsonResult($result, $data);
    }

    public function testRestartWithJobNotFound()
    {
        $this->serverMock->expects($this->once())->method('restart')->with('jobId')->willReturn(null);

        $response = $this->subject->restart('jobId', 'requestUri');

        $this->assertStatusCode(404, $response);
        $this->assertNotFoundResponse($response, 'jobId');
    }

    public function testRestartWithServerException()
    {
        $this->serverMock->expects($this->once())->method('restart')->willThrowException(new \Exception());

        $response = $this->subject->restart('jobId', 'requestUri');

        $this->assertServerErrorResponse($response);
    }

    public function testResult()
    {
        $result = static::buildResult();

        $this->serverMock->expects($this->once())->method('result')->with('jobId')->willReturn($result);

        $response = $this->subject->result('jobId', 'requestUri');

        $this->assertStatusCode(200, $response);
        $this->assertStdJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertJsonResult($result, $data);
    }

    public function testResultWithJobNotFound()
    {
        $this->serverMock->expects($this->once())->method('result')->with('jobId')->willReturn(null);

        $response = $this->subject->result('jobId', 'requestUri');

        $this->assertStatusCode(404, $response);
        $this->assertNotFoundResponse($response, 'jobId');
    }

    public function testResultWithServerException()
    {
        $this->serverMock->expects($this->once())->method('result')->willThrowException(new \Exception());

        $response = $this->subject->result('jobId', 'requestUri');

        $this->assertServerErrorResponse($response);
    }

    public function testCancel()
    {
        $this->serverMock->expects($this->once())->method('cancel')->with('jobId')->willReturn(true);

        $response = $this->subject->cancel('jobId', 'requestUri');

        $this->assertStatusCode(204, $response);
        $this->assertStdJsonResponseHeader($response);
    }

    public function testCancelWithJobNotFound()
    {
        $this->serverMock->expects($this->once())->method('cancel')->with('jobId')->willReturn(null);

        $response = $this->subject->cancel('jobId', 'requestUri');

        $this->assertStatusCode(404, $response);
        $this->assertNotFoundResponse($response, 'jobId');
    }

    public function testCancelWithServerException()
    {
        $this->serverMock->expects($this->once())->method('cancel')->willThrowException(new \Exception());

        $response = $this->subject->cancel('jobId', 'requestUri');

        $this->assertServerErrorResponse($response);
    }

    public function testCancelWithServerReturnsFalse()
    {
        $this->serverMock->expects($this->once())->method('cancel')->with('jobId')->willReturn(false);

        $response = $this->subject->cancel('jobId', 'requestUri');

        $this->assertStatusCode(406, $response);
        $this->assertProblemJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals(JobController::TYPE_URL.'cancellation-failed', $data['type']);
        $this->assertEquals('Job Cancellation Failed', $data['title']);
        $this->assertEquals(406, $data['status']);
        $this->assertEquals('Cancellation of job "jobId" failed', $data['detail']);
    }

    public function testDelete()
    {
        $this->serverMock->expects($this->once())->method('delete')->with('jobId')->willReturn(true);

        $response = $this->subject->delete('jobId', 'requestUri');

        $this->assertStatusCode(204, $response);
        $this->assertStdJsonResponseHeader($response);
    }

    public function testDeleteWithJobNotFound()
    {
        $this->serverMock->expects($this->once())->method('delete')->with('jobId')->willReturn(null);

        $response = $this->subject->delete('jobId', 'requestUri');

        $this->assertStatusCode(404, $response);
        $this->assertNotFoundResponse($response, 'jobId');
    }

    public function testDeleteWithServerException()
    {
        $this->serverMock->expects($this->once())->method('delete')->willThrowException(new \Exception());

        $response = $this->subject->delete('jobId', 'requestUri');

        $this->assertServerErrorResponse($response);
    }

    public static function buildResult(): Result
    {
        return new Result(static::createManagedJob());
    }

    private static function createManagedJob(): JobInterface
    {
        $job = new Job();
        $job->setId(Uuid::uuid4());
        $job->setType(Type::JOB());
        $job->setName('jobName');
        $job->setStatus(Status::COMPLETE);
        $job->setInput('input');
        $job->setOutput('someOutPut');
        $job->setProcessingTime(0.123);
        $job->setExternalId('externalId');
        $job->setCompletedAt(new \DateTime("@10"));
        $job->setUpdatedAt(new \DateTime("@100"));
        $job->setCreatedAt(new \DateTime("@1000"));

        return $job;
    }
}
