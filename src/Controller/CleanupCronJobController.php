<?php

namespace Abc\Job\Controller;

use Abc\Job\CronJobManager;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use OpenApi\Annotations as OA;

class CleanupCronJobController extends AbstractController
{
    /**
     * @var CronJobManager
     */
    private $cronJobManager;

    private $logger;

    public function __construct(CronJobManager $cronJobManager, LoggerInterface $logger)
    {
        $this->cronJobManager = $cronJobManager;
        $this->logger = $logger;
    }

    /**
     * @OA\Delete(
     *     path="/cronjob",
     *     tags={"Cleanup"},
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
    public function execute(string $requestUri): ResponseInterface
    {
        return $this->call(function () use ($requestUri) {

            $num = $this->cronJobManager->deleteAll();

            $this->logger->notice(sprintf('[DeleteController] Deleted %s cronjobs', $num));

            return new Response(204, static::$headers_ok);
        }, $requestUri, $this->logger);
    }
}
