<?php

namespace Abc\Job\Controller;

use Abc\ApiProblem\ApiProblem;
use Abc\Job\Broker\RegistryInterface;
use GuzzleHttp\Psr7\Response;
use OpenApi\Annotations as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class BrokerController extends AbstractController
{
    private const RESOURCE_NAME = 'Broker';

    /**
     * @var RegistryInterface
     */
    private $registry;

    public function __construct(RegistryInterface $registry, LoggerInterface $logger)
    {
        parent::__construct($logger);

        $this->registry = $registry;
    }

    /**
     * @OA\Put(
     *     path="/broker/{name}/setup",
     *     tags={"Job"},
     *     description="Sets up a broker by creating the queues for all defined routes.",
     *     summary="Setup a broker",
     *     @OA\Parameter(
     *         description="The unique name of the broker",
     *         in="path",
     *         name="name",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="In case of success",
     *         @OA\JsonContent(ref="#/components/schemas/Broker")
     *     ),
     *     @OA\Response(
     *         response="4XX",
     *         description="In case of client errors",
     *         @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     ),
     *     @OA\Response(
     *         response="5XX",
     *         description="In case of server errors",
     *         @OA\JsonContent(ref="#/components/schemas/ApiProblem")
     *     )
     * )
     *
     * @param string $name
     * @param string $requestUrix
     * @return ResponseInterface
     */
    public function setup(string $name, string $requestUri): ResponseInterface
    {
        return $this->handleExceptions(
            function () use ($name, $requestUri) {
                if (!$this->registry->exists($name)) {
                    return $this->createNotFoundResponse($name, $this::RESOURCE_NAME, $requestUri);
                }

                if (0 === (int)$this->registry->get($name)->getRoutes()) {
                    return $this->createProblemResponse(
                        new ApiProblem(
                            self::buildTypeUrl('conflict'),
                            'Conflict',
                            409,
                            sprintf('No routes registered'),
                            $requestUri
                        )
                    );
                }

                $broker = $this->registry->get($name);
                $broker->setup();

                return new Response(
                    200, static::$headers_ok, json_encode(
                           [
                               'name' => 'someName',
                           ]
                       )
                );
            },
            $requestUri,
            $this->logger
        );
    }
}
