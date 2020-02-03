<?php

namespace Abc\Job\Tests\Client;

use Abc\ApiProblem\ApiProblemException;
use Abc\Job\Client\AbstractClient;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class AbstractClientTest extends TestCase
{
    public function testValidateResponseWithInvalidJson()
    {
        try {
            (new TestClient())->callValidateResponse(new Response(400, [], 'invalidJson', null, 'some reason'));
        } catch (ApiProblemException $exception) {

            $apiProblem = $exception->getApiProblem();

            $this->assertEquals('https://aboutcoders.com/abc-job/problem/undefined', $apiProblem->getType());
            $this->assertEquals(400, $apiProblem->getStatus());
            $this->assertEquals('some reason', $apiProblem->getTitle());
            $this->assertEquals('invalidJson', $apiProblem->getDetail());
        }
    }
}

class TestClient extends AbstractClient
{
    public function callValidateResponse(ResponseInterface $response)
    {
        $this->validateResponse($response);
    }
}
