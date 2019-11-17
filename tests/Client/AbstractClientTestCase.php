<?php

namespace Abc\Job\Tests\Client;

use Abc\ApiProblem\ApiProblem;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

abstract class AbstractClientTestCase extends TestCase
{
    protected function createApiProblemJson(): string
    {
        $apiProblem = new ApiProblem('type', 'title', 400, 'detail', 'instance');

        return $apiProblem->toJson();
    }

    protected function createRequestException(int $code, string $content = null): RequestException
    {
        $request = $this->createMock(RequestInterface::class);
        $response = new Response($code, [], $content);

        return new RequestException('someMessage', $request, $response);
    }
}
