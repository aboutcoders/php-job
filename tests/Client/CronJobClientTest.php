<?php

namespace Abc\Job\Tests\Client;

use Abc\ApiProblem\ApiProblemException;
use Abc\Job\Client\CronJobClient;
use Abc\Job\Client\CronJobHttpClient;
use Abc\Job\Job;
use Abc\Job\Type;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;

class CronJobClientTest extends AbstractClientTestCase
{
    /**
     * @var CronJobHttpClient|MockObject
     */
    private $httpClientMock;

    /**
     * @var CronJobClient
     */
    private $subject;

    public function setUp(): void
    {
        $this->httpClientMock = $this->createMock(CronJobHttpClient::class);

        $this->subject = new CronJobClient($this->httpClientMock);
    }

    public function testList()
    {
        $job = new Job(Type::JOB(), 'jobName');

        $cronJob = new \Abc\Job\Model\CronJob('* * * * *', $job);
        $cronJob->setId('someId');

        $json = sprintf('[%s]', $cronJob->toJson());

        $response = new Response(200, [], $json);

        $this->httpClientMock->expects($this->once())->method('list')->with([])->willReturn($response);

        $this->assertEquals([$cronJob], $this->subject->list());
    }

    public function testListWithHttpError()
    {
        $this->httpClientMock->expects($this->once())->method('list')->with([])->willReturn(new Response(400, [], $this->createApiProblemJson()));

        $this->expectException(ApiProblemException::class);

        $this->subject->list();
    }

    public function testFind()
    {
        $job = new Job(Type::JOB(), 'jobName');

        $cronJob = new \Abc\Job\Model\CronJob('* * * * *', $job);
        $cronJob->setId('someCronJobId');

        $response = new Response(200, [], $cronJob->toJson());

        $this->httpClientMock->expects($this->once())->method('find')->with('someCronJobId')->willReturn($response);

        $this->assertEquals($cronJob, $this->subject->find('someCronJobId'));
    }

    public function testFindWith404()
    {
        $this->httpClientMock->expects($this->once())->method('find')->with('someId')->willReturn(new Response(404, [], $this->createApiProblemJson()));

        $this->assertNull($this->subject->find('someId'));
    }

    public function testFindWithApiProblem()
    {
        $this->httpClientMock->expects($this->once())->method('find')->with('someId')->willReturn(new Response(500, [], $this->createApiProblemJson()));

        $this->expectException(ApiProblemException::class);

        $this->assertNull($this->subject->find('someId'));
    }

    public function testCreate()
    {
        $job = new Job(Type::JOB(), 'jobName');

        $cronJob = new \Abc\Job\Model\CronJob('* * * * *', $job);
        $cronJob->setId('someId');

        $callback = function ($json) {
            $cronJob = \Abc\Job\Model\CronJob::fromJson($json);

            Assert::assertEquals('* * * * *', $cronJob->getSchedule());
            Assert::assertEquals(Type::JOB(), $cronJob->getJob()->getType());
            Assert::assertEquals('jobName', $cronJob->getJob()->getName());

            return true;
        };

        $this->httpClientMock->expects($this->once())->method('create')->with($this->callback($callback))->willReturn(new Response(201, [], $cronJob->toJson()));

        $this->assertEquals($cronJob, $this->subject->create('* * * * *', $job));
    }

    public function testCreateWithHttpError()
    {
        $job = new Job(Type::JOB(), 'jobName');

        $this->httpClientMock->expects($this->once())->method('create')->willReturn(new Response(400, [], $this->createApiProblemJson()));

        $this->expectException(ApiProblemException::class);

        $this->subject->create('* * * * *', $job);
    }

    public function testUpdate()
    {
        $job = new Job(Type::JOB(), 'jobName');

        $returnedCronJob = new \Abc\Job\Model\CronJob('updatedCron', $job);
        $returnedCronJob->setId('someId');

        $callback = function ($json) {
            $cronJob = \Abc\Job\Model\CronJob::fromJson($json);

            Assert::assertEquals('* * * * *', $cronJob->getSchedule());
            Assert::assertEquals(Type::JOB(), $cronJob->getJob()->getType());
            Assert::assertEquals('jobName', $cronJob->getJob()->getName());

            return true;
        };

        $this->httpClientMock->expects($this->once())->method('update')->with('someId', $this->callback($callback))->willReturn(new Response(200, [], $returnedCronJob->toJson()));

        $this->assertEquals($returnedCronJob, $this->subject->update('someId', '* * * * *', $job));
    }

    public function testUpdateWithHttpError()
    {
        $job = new Job(Type::JOB(), 'jobName');

        $this->httpClientMock->expects($this->once())->method('update')->willReturn(new Response(400, [], $this->createApiProblemJson()));

        $this->expectException(ApiProblemException::class);

        $this->subject->update('someId', '* * * * *', $job);
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
