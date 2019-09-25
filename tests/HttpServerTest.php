<?php

namespace Abc\Job\Tests;

use Abc\ApiProblem\InvalidParameter;
use Abc\Job\HttpServer;
use Abc\Job\Model\Job;
use Abc\Job\Model\JobInterface;
use Abc\Job\Result;
use Abc\Job\ServerInterface;
use Abc\Job\Status;
use Abc\Job\Type;
use Abc\Job\ValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;

class HttpServerTest extends TestCase
{
    /**
     * @var ServerInterface|MockObject
     */
    private $serverMock;

    /**
     * @var ValidatorInterface|MockObject
     */
    private $validatorMock;

    /**
     * @var HttpServer
     */
    private $subject;

    public function setUp(): void
    {
        $this->serverMock = $this->createMock(ServerInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->subject = new HttpServer($this->serverMock, $this->validatorMock, new NullLogger());
    }

    public function testIndexSuccess()
    {
        $queryString = 'foo=bar';

        $result = static::buildResult();

        $this->validatorMock->expects($this->once())->method('validate')->with('{"foo":"bar"}')->willReturn([]);

        $this->serverMock->expects($this->once())->method('find')->willReturn([$result]);

        $response = $this->subject->index($queryString, 'requestUri');

        $this->assertStatusCode(200, $response);
        $this->assertStdJsonResponseHeader($response);

        $json = $response->getBody()->getContents();
        $data = json_decode($json, true);

        $this->assertEquals(1, count($data));
        $this->assertJsonResult($result, $data[0]);
    }

    public function testIndexValidatorError()
    {
        $invalidParam = new InvalidParameter('name', 'reason', 'value');
        $queryString = 'foo=bar';

        $this->validatorMock->expects($this->once())->method('validate')->with('{"foo":"bar"}')->willReturn([$invalidParam]);

        $this->serverMock->expects($this->never())->method('find');

        $response = $this->subject->index($queryString, 'requestUri');

        $this->assertInvalidParameterResponse($response);
    }

    public function testIndexServerError()
    {
        $queryString = 'foo=bar';

        $this->validatorMock->expects($this->once())->method('validate')->with('{"foo":"bar"}')->willReturn([]);

        $this->serverMock->expects($this->once())->method('find')->willThrowException(new \Exception());

        $response = $this->subject->index($queryString, 'requestUri');

        $this->assertServerErrorResponse($response);
    }

    public function testProcessSuccess()
    {
        $result = static::buildResult();
        $job = new \Abc\Job\Job(Type::JOB(), 'jobName', 'input', [], true, 'externalId');
        $json = $job->toJson();

        $this->validatorMock->expects($this->once())->method('validate')->with($json)->willReturn([]);

        $this->serverMock->expects($this->once())->method('process')->with($this->equalTo($job))->willReturn($result);

        $response = $this->subject->process($json, 'requestUri');

        $this->assertStatusCode(201, $response);
        $this->assertStdJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertJsonResult($result, $data);
    }

    public function testProcessValidatorError()
    {
        $invalidParam = new InvalidParameter('name', 'reason', 'value');
        $job = new \Abc\Job\Job(Type::JOB(), 'jobName', 'input', [], true, 'externalId');
        $json = $job->toJson();

        $this->validatorMock->expects($this->once())->method('validate')->with($json)->willReturn([$invalidParam]);

        $this->serverMock->expects($this->never())->method('process');

        $response = $this->subject->process($json, 'requestUri');

        $this->assertInvalidParameterResponse($response);
    }

    public function testProcessServerError()
    {
        $job = new \Abc\Job\Job(Type::JOB(), 'jobName', 'input', [], true, 'externalId');
        $json = $job->toJson();

        $this->validatorMock->expects($this->once())->method('validate')->with($json)->willReturn([]);

        $this->serverMock->expects($this->once())->method('process')->willThrowException(new \Exception());

        $response = $this->subject->process($json, 'requestUri');

        $this->assertServerErrorResponse($response);
    }

    public function testRestartSuccess()
    {
        $result = static::buildResult();

        $this->serverMock->expects($this->once())->method('restart')->with('jobId')->willReturn($result);

        $response = $this->subject->restart('jobId', 'requestUri');

        $this->assertStatusCode(200, $response);
        $this->assertStdJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertJsonResult($result, $data);
    }

    public function testRestartNotFound()
    {
        $this->serverMock->expects($this->once())->method('restart')->with('jobId')->willReturn(null);

        $response = $this->subject->restart('jobId', 'requestUri');

        $this->assertStatusCode(404, $response);
        $this->assertNotFoundResponse($response);
    }

    public function testRestartServerError()
    {
        $this->serverMock->expects($this->once())->method('restart')->willThrowException(new \Exception());

        $response = $this->subject->restart('jobId', 'requestUri');

        $this->assertServerErrorResponse($response);
    }

    public function testResultSuccess()
    {
        $result = static::buildResult();

        $this->serverMock->expects($this->once())->method('result')->with('jobId')->willReturn($result);

        $response = $this->subject->result('jobId', 'requestUri');

        $this->assertStatusCode(200, $response);
        $this->assertStdJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertJsonResult($result, $data);
    }

    public function testResultNotFound()
    {
        $this->serverMock->expects($this->once())->method('result')->with('jobId')->willReturn(null);

        $response = $this->subject->result('jobId', 'requestUri');

        $this->assertStatusCode(404, $response);
        $this->assertNotFoundResponse($response);
    }

    public function testResultServerError()
    {
        $this->serverMock->expects($this->once())->method('result')->willThrowException(new \Exception());

        $response = $this->subject->result('jobId', 'requestUri');

        $this->assertServerErrorResponse($response);
    }

    public function testCancelSuccess()
    {
        $this->serverMock->expects($this->once())->method('cancel')->with('jobId')->willReturn(true);

        $response = $this->subject->cancel('jobId', 'requestUri');

        $this->assertStatusCode(204, $response);
        $this->assertStdJsonResponseHeader($response);
    }

    public function testCancelNotFound()
    {
        $this->serverMock->expects($this->once())->method('cancel')->with('jobId')->willReturn(null);

        $response = $this->subject->cancel('jobId', 'requestUri');

        $this->assertStatusCode(404, $response);
        $this->assertNotFoundResponse($response);
    }

    public function testCancelServerError()
    {
        $this->serverMock->expects($this->once())->method('cancel')->willThrowException(new \Exception());

        $response = $this->subject->cancel('jobId', 'requestUri');

        $this->assertServerErrorResponse($response);
    }

    public function testCancelFailure()
    {
        $this->serverMock->expects($this->once())->method('cancel')->with('jobId')->willReturn(false);

        $response = $this->subject->cancel('jobId', 'requestUri');

        $this->assertStatusCode(406, $response);
        $this->assertProblemJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals(HttpServer::TYPE_URL.'cancellation-failed', $data['type']);
        $this->assertEquals('Job Cancellation Failed', $data['title']);
        $this->assertEquals(406, $data['status']);
        $this->assertEquals('Cancellation of job "jobId" failed', $data['detail']);
    }

    public function testDeleteSuccess()
    {
        $this->serverMock->expects($this->once())->method('delete')->with('jobId')->willReturn(true);

        $response = $this->subject->delete('jobId', 'requestUri');

        $this->assertStatusCode(204, $response);
        $this->assertStdJsonResponseHeader($response);
    }

    public function testDeleteNotFound()
    {
        $this->serverMock->expects($this->once())->method('delete')->with('jobId')->willReturn(null);

        $response = $this->subject->delete('jobId', 'requestUri');

        $this->assertStatusCode(404, $response);
        $this->assertNotFoundResponse($response);
    }

    public function testDeleteServerError()
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

    private function assertStdJsonResponseHeader(ResponseInterface $response)
    {
        $this->assertEquals(['application/json'], $response->getHeader('Content-Type'));
    }

    private function assertProblemJsonResponseHeader(ResponseInterface $response)
    {
        $this->assertEquals(['application/problem+json'], $response->getHeader('Content-Type'));
    }

    private function assertStatusCode(int $statusCode, ResponseInterface $response)
    {
        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    private function assertJsonResult(Result $result, array $data)
    {
        $this->assertEquals($data['id'], $result->getId());
        $this->assertEquals($data['type'], $result->getType());
        $this->assertEquals($data['name'], $result->getName());
        $this->assertEquals($data['status'], $result->getStatus());
        $this->assertEquals($data['input'], $result->getInput());
        $this->assertEquals($data['output'], $result->getOutput());
        $this->assertEquals($data['processingTime'], $result->getProcessingTime());
        $this->assertEquals($data['externalId'], $result->getExternalId());
        $this->assertEquals($data['completed'], '1970-01-01T00:00:10+00:00');
        $this->assertEquals($data['updated'], '1970-01-01T00:01:40+00:00');
        $this->assertEquals($data['created'], '1970-01-01T00:16:40+00:00');
    }

    private function assertInvalidParameterResponse(ResponseInterface $response)
    {
        $this->assertStatusCode(400, $response);
        $this->assertProblemJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals(HttpServer::TYPE_URL.'invalid-parameters', $data['type']);
        $this->assertEquals('Your request parameters didn\'t validate.', $data['title']);
        $this->assertEquals(400, $data['status']);
        $this->assertEquals('One or more parameters are invalid.', $data['detail']);
        $this->assertEquals('requestUri', $data['instance']);
        $this->assertEquals('name', $data['invalid-params'][0]['name']);
        $this->assertEquals('reason', $data['invalid-params'][0]['reason']);
        $this->assertEquals('value', $data['invalid-params'][0]['value']);
    }

    private function assertNotFoundResponse(ResponseInterface $response)
    {
        $this->assertStatusCode(404, $response);
        $this->assertProblemJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals(HttpServer::TYPE_URL.'resource-not-found', $data['type']);
        $this->assertEquals('Resource Not Found', $data['title']);
        $this->assertEquals(404, $data['status']);
        $this->assertEquals('Job with id "jobId" not found', $data['detail']);
        $this->assertEquals('requestUri', $data['instance']);
    }

    private function assertServerErrorResponse(ResponseInterface $response)
    {
        $this->assertStatusCode(500, $response);
        $this->assertProblemJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals(HttpServer::TYPE_URL.'internal-error', $data['type']);
        $this->assertEquals('Internal Server Error', $data['title']);
        $this->assertEquals(500, $data['status']);
        $this->assertEquals('An internal server error occurred', $data['detail']);
        $this->assertEquals('requestUri', $data['instance']);
    }
}
