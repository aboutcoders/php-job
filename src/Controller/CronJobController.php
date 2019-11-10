<?php

namespace Abc\Job\Controller;

use Abc\ApiProblem\ApiProblem;
use Abc\Job\CronJob;
use Abc\Job\CronJobFilter;
use Abc\Job\CronJobManager;
use Abc\Job\Util\CronJobArray;
use Abc\Job\ValidatorInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use OpenApi\Annotations as OA;

class CronJobController extends AbstractController
{
    /**
     * @var CronJobManager
     */
    private $cronJobManager;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CronJobManager $scheduleManager,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ) {
        $this->cronJobManager = $scheduleManager;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * @OA\Get(
     *     path="/cronjob",
     *     tags={"CronJob"},
     *     description="Returns a list of cronjobs",
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
        return $this->call(function () use ($queryString, $requestUri) {

            if (null != $queryString) {
                parse_str($queryString, $data);
                $json = json_encode((object) $data);

                $invalidParams = $this->validator->validate($json, CronJobFilter::class);
                if (0 < count($invalidParams)) {
                    return $this->createInvalidParamResponse($invalidParams, $requestUri);
                }
            }

            $filter = CronJobFilter::fromQueryString($queryString);

            $cronJobs = $this->cronJobManager->list($filter);

            return new Response(200, static::$headers_ok, CronJobArray::toJson($cronJobs));
        }, $requestUri);
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
        return $this->call(function () use ($id, $requestUri) {

            $managedCronJob = $this->cronJobManager->find($id);
            if (null === $managedCronJob) {
                return $this->createNotFoundResponse($id, $requestUri);
            }

            return new Response(200, static::$headers_ok, $managedCronJob->toJson());
        }, $requestUri);
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
        return $this->call(function () use ($json, $requestUri) {

            $invalidParams = $this->validator->validate($json, CronJob::class);
            if (0 < count($invalidParams)) {
                return $this->createInvalidParamResponse($invalidParams, $requestUri);
            }

            $cronJob = \Abc\Job\Model\CronJob::fromJson($json);

            $managedCronJob = $this->cronJobManager->create($cronJob->getSchedule(), $cronJob->getJob());

            return new Response(201, static::$headers_ok, $managedCronJob->toJson());
        }, $requestUri);
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
        return $this->call(function () use ($id, $json, $requestUri) {

            $invalidParams = $this->validator->validate($json, CronJob::class);
            if (0 < count($invalidParams)) {
                return $this->createInvalidParamResponse($invalidParams, $requestUri);
            }

            $managedCronJob = $this->cronJobManager->find($id);
            if (null === $managedCronJob) {
                return $this->createNotFoundResponse($id, $requestUri);
            }

            $cronJob = \Abc\Job\Model\CronJob::fromJson($json);
            $managedCronJob->setSchedule($cronJob->getSchedule());
            $managedCronJob->setJob($cronJob->getJob());

            $this->cronJobManager->update($managedCronJob);

            return new Response(201, static::$headers_ok, $managedCronJob->toJson());
        }, $requestUri);
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
        return $this->call(function () use ($id, $requestUri) {

            $managedCronJob = $this->cronJobManager->find($id);
            if (null === $managedCronJob) {
                return $this->createNotFoundResponse($id, $requestUri);
            }

            $this->cronJobManager->delete($managedCronJob);

            return new Response(204, static::$headers_ok);
        }, $requestUri);
    }

    /**
     * @param \Closure $action
     * @param string $requestUri
     * @return mixed
     */
    private function call(\Closure $action, string $requestUri)
    {
        try {
            return $action();
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('[CronJobController] %s [%s](code: %s) at %s line: %s', $exception->getMessage(), get_class($exception), $exception->getCode(), $exception->getFile(), $exception->getLine()));

            $apiProblem = new ApiProblem(self::buildTypeUrl('internal-error'), 'Internal Server Error', 500, 'An internal server error occurred', $requestUri);

            return $this->createProblemResponse($apiProblem);
        }
    }
}
