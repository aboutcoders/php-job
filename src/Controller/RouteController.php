<?php

namespace Abc\Job\Controller;

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

    public function __construct(
        RouteRegistryInterface $registry,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);

        $this->registry = $registry;
        $this->validator = $validator;
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
    public function list(string $requestUri): ResponseInterface
    {
        return $this->handleExceptions(
            function () use ($requestUri) {
                $routes = [];
                foreach ($this->registry->all() as $route) {
                    $routes[] = (object)$route->toArray();
                }

                return new Response(200, static::$headers_ok, json_encode($routes));
            },
            $requestUri,
            $this->logger
        );
    }

    /**
     * @OA\Post(
     *     path="/route",
     *     tags={"Route"},
     *     description="Creates one or more routes",
     *     @OA\RequestBody(
     *         description="Route object to be created",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Route"),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation")
     *     ),
     * @OA\Response(
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
    public function set(string $json, string $requestUri): ResponseInterface
    {
        return $this->handleExceptions(
            function () use ($json, $requestUri) {
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
            },
            $requestUri,
            $this->logger
        );
    }
}
