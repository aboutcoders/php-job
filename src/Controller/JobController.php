<?php

namespace Abc\Job\Controller;

use Abc\ApiProblem\ApiProblem;
use Abc\Job\Job;
use Abc\Job\JobFilter;
use Abc\Job\JobServerInterface;
use Abc\Job\NoRouteException;
use Abc\Job\Util\ResultArray;
use Abc\Job\ValidatorInterface;
use GuzzleHttp\Psr7\Response;
use OpenApi\Annotations as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * @OA\Info(title="Job API", version="0.1")
 */
class JobController extends AbstractController
{
    private const RESOURCE_NAME = 'Job';

    /**
     * @var JobServerInterface
     */
    private $server;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(JobServerInterface $server, ValidatorInterface $validator, LoggerInterface $logger)
    {
        parent::__construct($logger);

        $this->server = $server;
        $this->validator = $validator;
    }

    /**
     * @OA\Get(
     *     path="/job",
     *     tags={"Job"},
     *     description="Returns a list of job results",
     *     @OA\Parameter(
     *         ref="#/components/parameters/ids"
     *     ),
     *     @OA\Parameter(
     *         ref="#/components/parameters/names"
     *     ),
     *     @OA\Parameter(
     *         ref="#/components/parameters/status"
     *     ),
     *     @OA\Parameter(
     *         ref="#/components/parameters/externalIds"
     *     ),
     *     @OA\Parameter(
     *         ref="#/components/parameters/latest"
     *     ),
     *     @OA\Parameter(
     *         ref="#/components/parameters/offset"
     *     ),
     *     @OA\Parameter(
     *         ref="#/components/parameters/limit"
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Result")
     *         )
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
    public function list(?string $queryString, string $requestUri): ResponseInterface
    {
        return $this->handleExceptions(
            function () use ($queryString, $requestUri) {
                if (null != $queryString) {
                    parse_str($queryString, $data);
                    $json = json_encode((object)$data);

                    $invalidParams = $this->validator->validate($json, JobFilter::class);
                    if (0 < count($invalidParams)) {
                        return $this->createInvalidParamResponse($invalidParams, $requestUri);
                    }
                }

                $results = $this->server->list(JobFilter::fromQueryString($queryString));

                return new Response(200, static::$headers_ok, ResultArray::toJson($results));
            },
            $requestUri,
            $this->logger
        );
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
        return $this->handleExceptions(
            function () use ($id, $requestUri) {
                $result = $this->server->result($id);

                if (null == $result) {
                    return $this->createNotFoundResponse($id, $this::RESOURCE_NAME, $requestUri);
                }

                return new Response(200, static::$headers_ok, $result->toJson());
            },
            $requestUri,
            $this->logger
        );
    }

    /**
     * @OA\Post(
     *     path="/job",
     *     tags={"Job"},
     *     description="Processes a job",
     *     @OA\RequestBody(
     *         description="Job object to be created",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Job"),
     *     ),
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
        return $this->handleExceptions(
            function () use ($json, $requestUri) {
                $invalidParams = $this->validator->validate($json, Job::class);
                if (0 < count($invalidParams)) {
                    return $this->createInvalidParamResponse($invalidParams, $requestUri);
                }

                $job = Job::fromJson($json);

                try {
                    $result = $this->server->process($job);
                } catch (NoRouteException $e) {
                    $apiProblem = new ApiProblem(
                        static::buildTypeUrl('no-route'),
                        'No Route Found',
                        400,
                        sprintf('No route found for job "%s"', $job->getName()),
                        $requestUri
                    );

                    return $this->createProblemResponse($apiProblem);
                }

                return new Response(201, static::$headers_ok, $result->toJson());
            },
            $requestUri,
            $this->logger
        );
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
        return $this->handleExceptions(
            function () use ($id, $requestUri) {
                $result = $this->server->restart($id);

                if (null == $result) {
                    return $this->createNotFoundResponse($id, $this::RESOURCE_NAME, $requestUri);
                }

                return new Response(200, static::$headers_ok, $result->toJson());
            },
            $requestUri,
            $this->logger
        );
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
        return $this->handleExceptions(
            function () use ($id, $requestUri) {
                $success = $this->server->cancel($id);

                if (null === $success) {
                    return $this->createNotFoundResponse($id, $this::RESOURCE_NAME, $requestUri);
                }

                if (false === $success) {
                    $apiProblem = new ApiProblem(
                        static::buildTypeUrl('cancellation-failed'),
                        'Job Cancellation Failed',
                        406,
                        sprintf('Cancellation of job "%s" failed', $id),
                        $requestUri
                    );

                    return $this->createProblemResponse($apiProblem);
                }

                return new Response(204, static::$headers_ok);
            },
            $requestUri,
            $this->logger
        );
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
        return $this->handleExceptions(
            function () use ($id, $requestUri) {
                $success = $this->server->delete($id);
                if (null == $success) {
                    return $this->createNotFoundResponse($id, $this::RESOURCE_NAME, $requestUri);
                };

                return new Response(204, static::$headers_ok);
            },
            $requestUri,
            $this->logger
        );
    }
}
