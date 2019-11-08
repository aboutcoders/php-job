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
