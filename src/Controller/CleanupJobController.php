<?php

namespace Abc\Job\Controller;

use Abc\Job\Model\JobManagerInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use OpenApi\Annotations as OA;

class CleanupJobController extends AbstractController
{
    /**
     * @var JobManagerInterface
     */
    private $jobManager;

    public function __construct(JobManagerInterface $jobManager, LoggerInterface $logger)
    {
        parent::__construct($logger);

        $this->jobManager = $jobManager;
    }

    /**
     * @OA\Delete(
     *     path="/job",
     *     tags={"Cleanup"},
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
    public function execute(string $requestUri): ResponseInterface
    {
        return $this->handleExceptions(function () use ($requestUri) {

            $num = $this->jobManager->deleteAll();

            $this->logger->notice(sprintf('[DeleteController] Deleted %s jobs', $num));

            return new Response(204, static::$headers_ok);
        }, $requestUri, $this->logger);
    }
}
