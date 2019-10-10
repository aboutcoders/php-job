<?php

namespace Abc\Job;

use Abc\Job\Model\RouteInterface;
use Abc\Job\Model\RouteManagerInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;

/**
 * HTTP API to manage routes for jobs
 */
class RouteHttpServer extends HttpServer
{
    /**
     * @var RouteManagerInterface
     */
    private $routeManager;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function add(string $json, string $requestUri): ResponseInterface
    {
        $invalidParams = $this->validator->validate($json, RouteInterface::class);
        if (0 < count($invalidParams)) {
            return $this->createInvalidParamResponse($invalidParams, $requestUri);
        }

        $decoded = json_decode($json);

        if(!is_array($decoded))
        {
            foreach ($decoded as $rawRoute)
            {
                $this->doAdd($this->createRoute($rawRoute));
            }
        }
        else {
            $this->doAdd($this->createRoute($decoded));
        }
    }

    private function createRoute(stdClass $rawRoute): RouteInterface
    {
        return $this->routeManager->create($rawRoute->jobName, $rawRoute->queueName ?? null, $rawRoute->replyTo ?? null);
    }
}
