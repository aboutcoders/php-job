<?php

namespace Abc\Job\Tests\Client;

use Abc\ApiProblem\ApiProblemException;
use Abc\Job\Client\HttpJobClient;
use Abc\Job\Client\JobClient;
use Abc\Job\JobFilter;
use Abc\Job\Job;
use Abc\Job\Result;
use Abc\Job\Tests\DataProvider\JobProvider;
use Abc\Job\Type;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;

class JobClientTest extends AbstractClientTestCase
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

    public function testList()
    {
        $job = JobProvider::createJob('myExternalId');

        $result = new Result($job);

        $json = json_encode([(object) $result->toArray()]);

        $response = new Response(200, [], $json);

        $this->httpClientMock->expects($this->once())->method('list')->with([])->willReturn($response);

        $this->assertEquals([$result], $this->subject->list());
    }

    public function testListWithFilter()
    {
        $filter = new JobFilter();
        $filter->setNames(['foo']);

        $result = new Result(JobProvider::createJob());

        $json = json_encode([(object) $result->toArray()]);

        $response = new Response(200, [], $json);

        $this->httpClientMock->expects($this->once())->method('list')->with(['name' => 'foo'])->willReturn($response);

        $this->assertEquals([$result], $this->subject->list($filter));
    }

    public function testListWithHttpError()
    {
        $this->httpClientMock->expects($this->once())->method('list')->with([])->willReturn(new Response(400, [], $this->createApiProblemJson()));

        $this->expectException(ApiProblemException::class);

        $this->subject->list();
    }

    public function testProcess()
    {
        $job = new Job(Type::JOB(), 'jobName');

        $result = new Result(JobProvider::createJob());

        $response = new Response(200, [], $result->toJson());

        $this->httpClientMock->expects($this->once())->method('process')->with($job->toJson())->willReturn($response);

        $this->assertEquals($result, $this->subject->process($job));
    }

    public function testProcessWithHttpError()
    {
        $job = new Job(Type::JOB(), 'jobName');

        $this->httpClientMock->expects($this->once())->method('process')->with($job->toJson())->willReturn(new Response(400, [], $this->createApiProblemJson()));

        $this->expectException(ApiProblemException::class);

        $this->subject->process($job);
    }

    public function testResult()
    {
        $result = new Result(JobProvider::createJob());

        $response = new Response(200, [], $result->toJson());

        $this->httpClientMock->expects($this->once())->method('result')->with('someId')->willReturn($response);

        $this->assertEquals($result, $this->subject->result('someId'));
    }

    public function testResultWith404()
    {
        $this->httpClientMock->expects($this->once())->method('result')->with('someId')->willReturn(new Response(404, [], $this->createApiProblemJson()));

        $this->assertNull($this->subject->result('someId'));
    }

    public function testResultWithHttpError()
    {
        $this->httpClientMock->expects($this->once())->method('result')->with('someId')->willReturn(new Response(400, [], $this->createApiProblemJson()));

        $this->expectException(ApiProblemException::class);

        $this->subject->result('someId');
    }

    public function testRestart()
    {
        $result = new Result(JobProvider::createJob());

        $response = new Response(200, [], $result->toJson());

        $this->httpClientMock->expects($this->once())->method('restart')->with('someId')->willReturn($response);

        $this->assertEquals($result, $this->subject->restart('someId'));
    }

    public function testRestartWith404()
    {
        $this->httpClientMock->expects($this->once())->method('restart')->with('someId')->willReturn(new Response(404, [], $this->createApiProblemJson()));

        $this->assertNull($this->subject->restart('someId'));
    }

    public function testRestartWithHttpError()
    {
        $this->httpClientMock->expects($this->once())->method('restart')->with('someId')->willReturn(new Response(400, [], $this->createApiProblemJson()));

        $this->expectException(ApiProblemException::class);

        $this->subject->restart('someId');
    }

    public function testCancel()
    {
        $response = new Response(200);

        $this->httpClientMock->expects($this->once())->method('cancel')->with('someId')->willReturn($response);

        $this->assertTrue($this->subject->cancel('someId'));
    }

    public function testCancelWith404()
    {
        $this->httpClientMock->expects($this->once())->method('cancel')->with('someId')->willReturn(new Response(404, [], $this->createApiProblemJson()));

        $this->assertNull($this->subject->cancel('someId'));
    }

    public function testCancelWith406()
    {
        $this->httpClientMock->expects($this->once())->method('cancel')->with('someId')->willReturn(new Response(406, [], $this->createApiProblemJson()));

        $this->assertFalse($this->subject->cancel('someId'));
    }

    public function testCancelWithHttpError()
    {
        $this->httpClientMock->expects($this->once())->method('cancel')->with('someId')->willReturn(new Response(400, [], $this->createApiProblemJson()));

        $this->expectException(ApiProblemException::class);

        $this->subject->cancel('someId');
    }

    public function testDelete()
    {
        $response = new Response(204);

        $this->httpClientMock->expects($this->once())->method('delete')->with('someId')->willReturn($response);

        $this->assertTrue($this->subject->delete('someId'));
    }

    public function testDeleteWith404()
    {
        $this->httpClientMock->expects($this->once())->method('delete')->with('someId')->willReturn(new Response(404, [], $this->createApiProblemJson()));

        $this->assertNull($this->subject->delete('someId'));
    }

    public function testDeleteWithHttpError()
    {
        $this->httpClientMock->expects($this->once())->method('delete')->with('someId')->willReturn(new Response(400, [], $this->createApiProblemJson()));

        $this->expectException(ApiProblemException::class);

        $this->subject->delete('someId');
    }
}
