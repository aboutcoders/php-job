<?php

namespace Abc\Job\Tests\Controller;

use Abc\Job\Controller\JobController;
use Abc\Job\Result;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractControllerTestCase extends TestCase
{
    protected function assertStdJsonResponseHeader(ResponseInterface $response)
    {
        $this->assertEquals(['application/json'], $response->getHeader('Content-Type'));
    }

    protected function assertProblemJsonResponseHeader(ResponseInterface $response)
    {
        $this->assertEquals(['application/problem+json'], $response->getHeader('Content-Type'));
    }

    protected function assertStatusCode(int $statusCode, ResponseInterface $response)
    {
        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    protected function assertServerErrorResponse(ResponseInterface $response)
    {
        $this->assertStatusCode(500, $response);
        $this->assertProblemJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals(JobController::TYPE_URL.'internal-error', $data['type']);
        $this->assertEquals('Internal Server Error', $data['title']);
        $this->assertEquals(500, $data['status']);
        $this->assertEquals('An internal server error occurred', $data['detail']);
        $this->assertEquals('requestUri', $data['instance']);
    }

    protected function assertInvalidJsonResponse(ResponseInterface $response, string $expectedProblemDetail)
    {
        $this->assertStatusCode(400, $response);
        $this->assertProblemJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals(JobController::TYPE_URL.'invalid-request-body', $data['type']);
        $this->assertEquals('The request body didn\'t validate.', $data['title']);
        $this->assertEquals(400, $data['status']);
        $this->assertEquals($expectedProblemDetail, $data['detail']);
        $this->assertEquals('requestUri', $data['instance']);
    }

    protected function assertInvalidParameterResponse(ResponseInterface $response)
    {
        $this->assertStatusCode(400, $response);
        $this->assertProblemJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals(JobController::TYPE_URL.'invalid-parameters', $data['type']);
        $this->assertEquals('Your request parameters didn\'t validate.', $data['title']);
        $this->assertEquals(400, $data['status']);
        $this->assertEquals('One or more parameters are invalid.', $data['detail']);
        $this->assertEquals('requestUri', $data['instance']);
        $this->assertEquals('name', $data['invalid-params'][0]['name']);
        $this->assertEquals('reason', $data['invalid-params'][0]['reason']);
        $this->assertEquals('value', $data['invalid-params'][0]['value']);
    }

    protected function assertNotFoundResponse(ResponseInterface $response, string $id)
    {
        $this->assertStatusCode(404, $response);
        $this->assertProblemJsonResponseHeader($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals(JobController::TYPE_URL.'resource-not-found', $data['type']);
        $this->assertEquals('Resource Not Found', $data['title']);
        $this->assertEquals(404, $data['status']);
        $this->assertEquals(sprintf('Job with id "%s" not found', $id), $data['detail']);
        $this->assertEquals('requestUri', $data['instance']);
    }

    protected function assertJsonResult(Result $result, array $data)
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
}
