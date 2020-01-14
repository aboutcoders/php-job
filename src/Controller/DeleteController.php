<?php

namespace Abc\Job\Controller;

use Abc\Job\Model\CronJobManagerInterface;
use Abc\Job\Model\JobManagerInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class DeleteController extends AbstractController
{
    /**
     * @var JobManagerInterface
     */
    private $jobManager;

    /**
     * @var CronJobManagerInterface
     */
    private $cronJobManager;

    private $logger;

    public function __construct(
        JobManagerInterface $jobManager,
        CronJobManagerInterface $cronJobManager,
        LoggerInterface $logger
    ) {
        $this->jobManager = $jobManager;
        $this->cronJobManager = $cronJobManager;
        $this->logger = $logger;
    }

    /**
     * @OA\Delete(
     *     path="/job",
     *     tags={"Job"},
     *     description="Deletes all jobs",
     *     @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *     ),
     *     @OA\Response(
     *          response=500,
     *          description="In case of an internal server error",
     *          @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     )
     * )
     *
     * @param string $requestUri
     * @return ResponseInterface
     */
    public function deleteJobs(string $requestUri): ResponseInterface
    {
        return $this->call(function () use ($requestUri) {

            $num = $this->jobManager->deleteAll();

            $this->logger->notice(sprintf('[DeleteController] Deleted %s jobs', $num));

            return new Response(204, static::$headers_ok);
        }, $requestUri, $this->logger);
    }

    /**
     * @OA\Delete(
     *     path="/cronjob",
     *     tags={"Job"},
     *     description="Deletes all cronjobs",
     *     @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *     ),
     *     @OA\Response(
     *          response=500,
     *          description="In case of an internal server error",
     *          @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     )
     * )
     *
     * @param string $requestUri
     * @return ResponseInterface
     */
    public function deleteCronJobs(string $requestUri): ResponseInterface
    {
        return $this->call(function () use ($requestUri) {

            $num = $this->cronJobManager->deleteAll();

            $this->logger->notice(sprintf('[DeleteController] Deleted %s cronjobs', $num));

            return new Response(204, static::$headers_ok);
        }, $requestUri, $this->logger);
    }
}
