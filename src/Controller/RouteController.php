<?php

namespace Abc\Job\Controller;

use Abc\ApiProblem\ApiProblem;
use Abc\Job\Broker\Route;
use Abc\Job\Broker\RouteRegistryInterface;
use Abc\Job\ValidatorInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * HTTP API to manage routes for jobs
 */
class RouteController extends AbstractController
{
    /**
     * @var RouteRegistryInterface
     */
    private $registry;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        RouteRegistryInterface $registry,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ) {
        $this->registry = $registry;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * @OA\Post(
     *     path="/route",
     *     tags={"Route"},
     *     description="Creates a route",
     *     @OA\RequestBody(
     *         description="Route object to be created",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Route"),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation")
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

            $invalidParams = $this->validator->validate($json, Route::class);
            if (0 < count($invalidParams)) {
                return $this->createInvalidParamResponse($invalidParams, $requestUri);
            }

            $rawRoute = json_decode($json, true);
            if (isset($rawRoute['name'])) {
                $this->registry->add(Route::fromArray($rawRoute));
            } else {
                $rawRoutes = $rawRoute;
                foreach ($rawRoutes as $rawRoute) {
                    $this->registry->add(Route::fromArray($rawRoute));
                }
            }

            return new Response(201);
        }, $requestUri);
    }

    /**
     * @OA\Get(
     *     path="/route",
     *     tags={"Route"},
     *     description="Returns a list of route objects",
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Route")
     *         ),
     *     ),
     *     @OA\Response(
     *          response=500,
     *          description="In case of an internal server error",
     *          @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     )
     * )
     * @param string $requestUri
     * @return ResponseInterface
     */
    public function all(string $requestUri): ResponseInterface
    {
        return $this->call(function () use ($requestUri) {

            $routes = [];
            foreach ($this->registry->all() as $route) {
                $routes[] = (object) $route->toArray();
            }

            return new Response(200, static::$headers_ok, json_encode($routes));
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
            return $action($this->registry);
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('[RouteController] %s [%s](code: %s) at %s line: %s', $exception->getMessage(), get_class($exception), $exception->getCode(), $exception->getFile(), $exception->getLine()));

            $apiProblem = new ApiProblem(self::buildTypeUrl('internal-error'), 'Internal Server Error', 500, 'An internal server error occurred', $requestUri);

            return $this->createProblemResponse($apiProblem);
        }
    }
}
