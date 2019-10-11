<?php

namespace Abc\Job\Tests;

use Abc\ApiProblem\ApiProblemException;
use Abc\Job\Filter;
use Abc\Job\HttpJobClient;
use Abc\Job\Job;
use Abc\Job\JobClient;
use Abc\Job\Result;
use Abc\Job\Tests\DataProvider\JobProvider;
use Abc\Job\Type;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;

class JobClientTest extends ClientTestCase
{
    /**
     * @var HttpJobClient|MockObject
     */
    private $httpClientMock;

    /**
     * @var JobClient
     */
    private $subject;

    public function setUp(): void
    {
        $this->httpClientMock = $this->createMock(HttpJobClient::class);

        $this->subject = new JobClient($this->httpClientMock);
    }

    public function testAll()
    {
        $job = JobProvider::createJob('myExternalId');

        $result = new Result($job);

        $json = json_encode([(object) $result->toArray()]);

        $response = new Response(200, [], $json);

        $this->httpClientMock->expects($this->once())->method('all')->with([], ['http_errors' => true])->willReturn($response);

        $this->assertEquals([$result], $this->subject->all());
    }

    public function testAllWithFilter()
    {
        $filter = new Filter();
        $filter->setNames(['foo']);

        $result = new Result(JobProvider::createJob());

        $json = json_encode([(object) $result->toArray()]);

        $response = new Response(200, [], $json);

        $this->httpClientMock->expects($this->once())->method('all')->with(['name' => 'foo'], ['http_errors' => true])->willReturn($response);

        $this->assertEquals([$result], $this->subject->all($filter));
    }

    public function testAllWithApiException()
    {
        $this->httpClientMock->expects($this->once())->method('all')->with([], ['http_errors' => true])->willThrowException($this->createRequestException(400, $this->createApiProblemJson()));

        $this->expectException(ApiProblemException::class);

        $this->subject->all();
    }

    public function testProcess()
    {
        $job = new Job(Type::JOB(), 'jobName');

        $result = new Result(JobProvider::createJob());

        $response = new Response(200, [], $result->toJson());

        $this->httpClientMock->expects($this->once())->method('process')->with($job->toJson(), ['http_errors' => true])->willReturn($response);

        $this->assertEquals($result, $this->subject->process($job));
    }

    public function testProcessWithApiException()
    {
        $job = new Job(Type::JOB(), 'jobName');

        $this->httpClientMock->expects($this->once())->method('process')->with($job->toJson(), ['http_errors' => true])->willThrowException($this->createRequestException(400, $this->createApiProblemJson()));

        $this->expectException(ApiProblemException::class);

        $this->subject->process($job);
    }

    public function testResult()
    {
        $result = new Result(JobProvider::createJob());

        $response = new Response(200, [], $result->toJson());

        $this->httpClientMock->expects($this->once())->method('result')->with('someId', ['http_errors' => true])->willReturn($response);

        $this->assertEquals($result, $this->subject->result('someId'));
    }

    public function testResultWith404()
    {
        $this->httpClientMock->expects($this->once())->method('result')->with('someId', ['http_errors' => true])->willThrowException($this->createRequestException(404));

        $this->assertNull($this->subject->result('someId'));
    }

    public function testResultWithApiException()
    {
        $this->httpClientMock->expects($this->once())->method('result')->with('someId', ['http_errors' => true])->willThrowException($this->createRequestException(400, $this->createApiProblemJson()));

        $this->expectException(ApiProblemException::class);

        $this->subject->result('someId');
    }

    public function testRestart()
    {
        $result = new Result(JobProvider::createJob());

        $response = new Response(200, [], $result->toJson());

        $this->httpClientMock->expects($this->once())->method('restart')->with('someId', ['http_errors' => true])->willReturn($response);

        $this->assertEquals($result, $this->subject->restart('someId'));
    }

    public function testRestartWith404()
    {
        $this->httpClientMock->expects($this->once())->method('restart')->with('someId', ['http_errors' => true])->willThrowException($this->createRequestException(404));

        $this->assertNull($this->subject->restart('someId'));
    }

    public function testRestartWithApiException()
    {
        $this->httpClientMock->expects($this->once())->method('restart')->with('someId', ['http_errors' => true])->willThrowException($this->createRequestException(400, $this->createApiProblemJson()));

        $this->expectException(ApiProblemException::class);

        $this->subject->restart('someId');
    }

    public function testCancel()
    {
        $response = new Response(200);

        $this->httpClientMock->expects($this->once())->method('cancel')->with('someId', ['http_errors' => true])->willReturn($response);

        $this->assertTrue($this->subject->cancel('someId'));
    }

    public function testCancelWith404()
    {
        $this->httpClientMock->expects($this->once())->method('cancel')->with('someId', ['http_errors' => true])->willThrowException($this->createRequestException(404));

        $this->assertNull($this->subject->cancel('someId'));
    }

    public function testCancelWith406()
    {
        $this->httpClientMock->expects($this->once())->method('cancel')->with('someId', ['http_errors' => true])->willThrowException($this->createRequestException(406));

        $this->assertFalse($this->subject->cancel('someId'));
    }

    public function testCancelWithApiException()
    {
        $this->httpClientMock->expects($this->once())->method('cancel')->with('someId', ['http_errors' => true])->willThrowException($this->createRequestException(400, $this->createApiProblemJson()));

        $this->expectException(ApiProblemException::class);

        $this->subject->cancel('someId');
    }

    public function testDelete()
    {
        $response = new Response(204);

        $this->httpClientMock->expects($this->once())->method('delete')->with('someId', ['http_errors' => true])->willReturn($response);

        $this->assertTrue($this->subject->delete('someId'));
    }

    public function testDeleteWith404()
    {
        $this->httpClientMock->expects($this->once())->method('delete')->with('someId', ['http_errors' => true])->willThrowException($this->createRequestException(404));

        $this->assertNull($this->subject->delete('someId'));
    }

    public function testDeleteWithApiException()
    {
        $this->httpClientMock->expects($this->once())->method('delete')->with('someId', ['http_errors' => true])->willThrowException($this->createRequestException(400, $this->createApiProblemJson()));

        $this->expectException(ApiProblemException::class);

        $this->subject->delete('someId');
    }
}
