<?php

namespace Abc\Job\Controller;

use Abc\ApiProblem\InvalidParameter;
use Abc\Job\CronJob;
use Abc\Job\CronJobFilter;
use Abc\Job\CronJobManager;
use Abc\Job\Util\CronJobArray;
use Abc\Job\ValidatorInterface;
use Cron\CronExpression;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use OpenApi\Annotations as OA;

class CronJobController extends AbstractController
{
    private const RESOURCE_NAME = 'CronJob';

    /**
     * @var CronJobManager
     */
    private $cronJobManager;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(CronJobManager $cronJobManager, ValidatorInterface $validator, LoggerInterface $logger)
    {
        parent::__construct($logger);

        $this->validator = $validator;
        $this->cronJobManager = $cronJobManager;
    }

    /**
     * @OA\Get(
     *     path="/cronjob",
     *     tags={"CronJob"},
     *     description="Returns a list of cronjobs",
     *     @OA\Parameter(
     *         description="The ids of the cronjob",
     *         in="query",
     *         name="ids",
     *         required=false,
     *         style="simple",
     *         explode="false",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(
     *                 type="string",
     *                 format="uuid"
     *             )
     *         )
     *     ),
     *     @OA\Parameter(
     *         ref="#/components/parameters/names"
     *     ),
     *     @OA\Parameter(
     *         ref="#/components/parameters/externalIds"
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
     *             @OA\Items(ref="#/components/schemas/CronJob")
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
     * @param string $id
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

                    $invalidParams = $this->validator->validate($json, CronJobFilter::class);
                    if (0 < count($invalidParams)) {
                        return $this->createInvalidParamResponse($invalidParams, $requestUri);
                    }
                }

                $filter = CronJobFilter::fromQueryString($queryString);

                $cronJobs = $this->cronJobManager->list($filter);

                return new Response(200, static::$headers_ok, CronJobArray::toJson($cronJobs));
            },
            $requestUri,
            $this->logger
        );
    }

    /**
     * @OA\Get(
     *     path="/cronjob/{id}",
     *     tags={"CronJob"},
     *     description="Returns a cronjob",
     *     @OA\Parameter(
     *         description="The unique id of the cronjob",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             ref="#components/schemas/CronJob/properties/id"
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/CronJob")
     *     ),
     *     @OA\Response(
     *          response=400,
     *          description="In case invalid parameters were provided",
     *          @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="In case a cronjob with the given id is not found",
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
    public function find(string $id, string $requestUri): ResponseInterface
    {
        return $this->handleExceptions(
            function () use ($id, $requestUri) {
                $managedCronJob = $this->cronJobManager->find($id);
                if (null === $managedCronJob) {
                    return $this->createNotFoundResponse($id, $this::RESOURCE_NAME, $requestUri);
                }

                return new Response(200, static::$headers_ok, $managedCronJob->toJson());
            },
            $requestUri,
            $this->logger
        );
    }

    /**
     * @OA\Post(
     *     path="/cronjob",
     *     tags={"CronJob"},
     *     description="Creates a cronjob",
     *     @OA\RequestBody(
     *         description="CronJob object to be created",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CronJob"),
     *     ),
     *     @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/CronJob")
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
    public function create(string $json, string $requestUri): ResponseInterface
    {
        return $this->handleExceptions(
            function () use ($json, $requestUri) {
                $invalidParams = $this->validator->validate($json, CronJob::class);
                if (0 < count($invalidParams)) {
                    return $this->createInvalidParamResponse($invalidParams, $requestUri);
                }

                $cronJob = \Abc\Job\Model\CronJob::fromJson($json);

                if (!CronExpression::isValidExpression($cronJob->getSchedule())) {
                    return $this->createInvalidParamResponse(
                        [new InvalidParameter('schedule', 'Invalid cron job expression', $cronJob->getSchedule())],
                        $requestUri
                    );
                }

                $managedCronJob = $this->cronJobManager->create($cronJob->getSchedule(), $cronJob->getJob(), $cronJob->getConcurrencyPolicy());

                return new Response(201, static::$headers_ok, $managedCronJob->toJson());
            },
            $requestUri,
            $this->logger
        );
    }

    /**
     * @OA\Put(
     *     path="/cronjob/{id}",
     *     tags={"CronJob"},
     *     description="Updates a cronjob",
     *     @OA\Parameter(
     *         description="The unique id of the cronjob",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             ref="#components/schemas/CronJob/properties/id"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="CronJob object to be updated",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CronJob"),
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/CronJob")
     *     ),
     *     @OA\Response(
     *          response=400,
     *          description="In case invalid parameters were provided",
     *          @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="In case a cronjob with the given id is not found",
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
    public function update(string $id, string $json, string $requestUri): ResponseInterface
    {
        return $this->handleExceptions(
            function () use ($id, $json, $requestUri) {
                $invalidParams = $this->validator->validate($json, CronJob::class);
                if (0 < count($invalidParams)) {
                    return $this->createInvalidParamResponse($invalidParams, $requestUri);
                }

                $managedCronJob = $this->cronJobManager->find($id);
                if (null === $managedCronJob) {
                    return $this->createNotFoundResponse($id, $this::RESOURCE_NAME, $requestUri);
                }

                $cronJob = \Abc\Job\Model\CronJob::fromJson($json);

                if (!CronExpression::isValidExpression($cronJob->getSchedule())) {
                    return $this->createInvalidParamResponse(
                        [new InvalidParameter('schedule', 'Invalid cron job expression', $cronJob->getSchedule())],
                        $requestUri
                    );
                }

                $managedCronJob->setSchedule($cronJob->getSchedule());
                $managedCronJob->setJob($cronJob->getJob());

                $this->cronJobManager->update($managedCronJob);

                return new Response(200, static::$headers_ok, $managedCronJob->toJson());
            },
            $requestUri,
            $this->logger
        );
    }

    /**
     * @OA\Delete(
     *     path="/cronjob/{id}",
     *     tags={"CronJob"},
     *     description="Deletes a cronjob",
     *     @OA\Parameter(
     *         description="The unique id of the cronjob",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             ref="#components/schemas/CronJob/properties/id"
     *         )
     *     ),
     *     @OA\Response(
     *          response=204,
     *          description="Successful operation"
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="In case a cronjob with the given id is not found",
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
                $managedCronJob = $this->cronJobManager->find($id);
                if (null === $managedCronJob) {
                    return $this->createNotFoundResponse($id, $this::RESOURCE_NAME, $requestUri);
                }

                $this->cronJobManager->delete($managedCronJob);

                return new Response(204, static::$headers_ok);
            },
            $requestUri,
            $this->logger
        );
    }
}
