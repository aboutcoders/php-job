<?php

namespace Abc\Job;

use Abc\ApiProblem\ApiProblem;
use Abc\Job\Util\ResultArray;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(title="ABC Job API", version="0.1")
 */
class HttpServer
{
    const TYPE_URL = 'https://aboutcoders.com/abc-job/problem/';

    private static $headers_ok = ['Content-Type' => 'application/json'];

    private static $headers_problem = ['Content-Type' => 'application/problem+json'];

    /**
     * @var ServerInterface
     */
    private $server;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Closure
     */
    private $exceptionLogger;

    public function __construct(
        ServerInterface $server,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ) {
        $this->server = $server;
        $this->validator = $validator;
        $this->logger = $logger;

        $this->exceptionLogger = function (\Exception $exception, LoggerInterface $logger) {
            $logger->error(sprintf('[HttpServer] %s [%s](code: %s) at %s line: %s', $exception->getMessage(), get_class($exception), $exception->getCode(), $exception->getFile(), $exception->getLine()));
        };
    }

    /**
     * @param \Closure $exceptionLogger A function that takes the arguments \Exception and \Psr\Log\LoggerInterface
     */
    public function setExceptionLogger(\Closure $exceptionLogger): void
    {
        $this->exceptionLogger = $exceptionLogger;
    }

    /**
     * @OA\Get(
     *     path="/job",
     *     tags={"Job"},
     *     description="Returns index of job results",
     *     @OA\Parameter(
     *         description="The unique id of the job",
     *         in="query",
     *         name="id",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="The status of the job",
     *         in="query",
     *         name="status",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"waiting", "scheduled", "running", "complete", "failed", "cancelled"},
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="The name of the job",
     *         in="query",
     *         name="name",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="The external id of the job",
     *         in="query",
     *         name="externalId",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Result")
     *         ),
     *     ),
     *     @OA\Response(
     *          response=400,
     *          description="In case invalid parameters were provided",
     *          @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     ),
     *     @OA\Response(
     *          response=500,
     *          description="In case of an internal server error",
     *          @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     )
     * )
     *
     * @param null|string $queryString
     * @param string $requestUri
     * @return ResponseInterface
     */
    public function index(?string $queryString, string $requestUri): ResponseInterface
    {
        if (null != $queryString) {
            parse_str($queryString, $data);
            $json = json_encode((object) $data);

            $invalidParams = $this->validator->validate($json, Filter::class);
            if (0 < count($invalidParams)) {
                return $this->createInvalidParamResponse($invalidParams, $requestUri);
            }
        }

        $filter = Filter::fromQueryString($queryString);

        return $this->call(function () use ($filter) {
            $results = $this->server->find($filter);

            return new Response(200, static::$headers_ok, ResultArray::toJson($results));
        }, $requestUri);
    }

    /**
     * @OA\Post(
     *     path="/job",
     *     tags={"Job"},
     *     description="Processes a job",
     *     @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Result")
     *     ),
     *     @OA\Response(
     *          response=400,
     *          description="In case invalid parameters were provided",
     *          @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     ),
     *     @OA\Response(
     *          response=500,
     *          description="In case of an internal server error",
     *          @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     )
     * )
     *
     * @param string $json
     * @param string $requestUri
     * @return ResponseInterface
     */
    public function process(string $json, string $requestUri): ResponseInterface
    {
        $invalidParams = $this->validator->validate($json, Job::class);
        if (0 < count($invalidParams)) {
            return $this->createInvalidParamResponse($invalidParams, $requestUri);
        }

        $job = Job::fromJson($json);

        return $this->call(function () use ($job) {
            $result = $this->server->process($job);

            return new Response(201, static::$headers_ok, $result->toJson());
        }, $requestUri);
    }

    /**
     * @OA\Get(
     *     path="/job/{id}",
     *     tags={"Job"},
     *     description="Returns the result of a job",
     *     @OA\Parameter(
     *         description="The unique id of the job",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Result")
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="In case the job with the given id is not found",
     *          @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     ),
     *     @OA\Response(
     *          response=500,
     *          description="In case of an internal server error",
     *          @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     )
     * )
     *
     * @param string $id
     * @param string $requestUri
     * @return ResponseInterface
     */
    public function result(string $id, string $requestUri): ResponseInterface
    {
        return $this->call(function () use ($id, $requestUri) {
            $result = $this->server->result($id);

            if (null == $result) {
                return $this->createNotFoundResponse($id, $requestUri);
            }

            return new Response(200, static::$headers_ok, $result->toJson());
        }, $requestUri);
    }

    /**
     * @OA\Put(
     *     path="/job/{id}/restart",
     *     tags={"Job"},
     *     description="Restarts a job",
     *     @OA\Parameter(
     *         description="The unique id of the job",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Result")
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="In case the job with the given id is not found",
     *          @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     ),
     *     @OA\Response(
     *          response=500,
     *          description="In case of an internal server error",
     *          @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     )
     * )
     *
     * @param string $id
     * @param string $requestUri
     * @return ResponseInterface
     */
    public function restart(string $id, string $requestUri): ResponseInterface
    {
        return $this->call(function () use ($id, $requestUri) {
            $result = $this->server->restart($id);

            if (null == $result) {
                return $this->createNotFoundResponse($id, $requestUri);
            }

            return new Response(200, static::$headers_ok, $result->toJson());
        }, $requestUri);
    }

    /**
     * @OA\Put(
     *     path="/job/{id}/cancel",
     *     tags={"Job"},
     *     description="Cancels a job",
     *     @OA\Parameter(
     *         description="The unique id of the job",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="In case the job with the given id is not found",
     *          @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     ),
     *     @OA\Response(
     *          response=406,
     *          description="In case the job could not be cancelled",
     *          @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     ),
     *     @OA\Response(
     *          response=500,
     *          description="In case of an internal server error",
     *          @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     )
     * )
     *
     * @param string $id
     * @param string $requestUri
     * @return ResponseInterface
     */
    public function cancel(string $id, string $requestUri): ResponseInterface
    {
        return $this->call(function () use ($id, $requestUri) {
            $success = $this->server->cancel($id);

            if (null === $success) {
                return $this->createNotFoundResponse($id, $requestUri);
            }

            if (false === $success) {
                $apiProblem = new ApiProblem(static::buildTypeUrl('cancellation-failed'), 'Job Cancellation Failed', 406, sprintf('Cancellation of job "%s" failed', $id), $requestUri);

                return $this->createProblemResponse($apiProblem);
            }

            return new Response(204, static::$headers_ok);
        }, $requestUri);
    }

    /**
     * @OA\Delete(
     *     path="/job/{id}",
     *     tags={"Job"},
     *     description="Deletes a job",
     *     @OA\Parameter(
     *         description="The unique id of the job",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="In case the job with the given id is not found",
     *          @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     ),
     *     @OA\Response(
     *          response=500,
     *          description="In case of an internal server error",
     *          @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     )
     * )
     *
     * @param string $id
     * @param string $requestUri
     * @return ResponseInterface
     */
    public function delete(string $id, string $requestUri): ResponseInterface
    {
        return $this->call(function () use ($id, $requestUri) {
            $success = $this->server->delete($id);
            if (null == $success) {
                return $this->createNotFoundResponse($id, $requestUri);
            };

            return new Response(204, static::$headers_ok);
        }, $requestUri);
    }

    /**
     * @param \Closure $serverAction
     * @param string $requestUri
     * @return mixed
     */
    private function call(\Closure $serverAction, string $requestUri)
    {
        try {
            return $serverAction($this->server);
        } catch (\Exception $exception) {
            ($this->exceptionLogger)($exception, $this->logger);

            $apiProblem = new ApiProblem(self::buildTypeUrl('internal-error'), 'Internal Server Error', 500, 'An internal server error occurred', $requestUri);

            return $this->createProblemResponse($apiProblem);
        }
    }

    private function createInvalidParamResponse(array $invalidParameters, $requestUri): ResponseInterface
    {
        $apiProblem = new ApiProblem(self::buildTypeUrl('invalid-parameters'), 'Your request parameters didn\'t validate.', 400, 'One or more parameters are invalid.', $requestUri);
        $apiProblem->setInvalidParams($invalidParameters);

        return $this->createProblemResponse($apiProblem);
    }

    private function createNotFoundResponse($id, $requestUri): ResponseInterface
    {
        $apiProblem = new ApiProblem(self::buildTypeUrl('resource-not-found'), 'Resource Not Found', 404, sprintf('Job with id "%s" not found', $id), $requestUri);

        return $this->createProblemResponse($apiProblem);
    }

    private function createProblemResponse(ApiProblem $apiProblem): ResponseInterface
    {
        return new Response($apiProblem->getStatus(), static::$headers_problem, $apiProblem->toJson());
    }

    private static function buildTypeUrl(string $problem): string
    {
        return self::TYPE_URL.$problem;
    }
}
