<?php

namespace Abc\Job\Broker;

use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;

/**
 * @OA\Schema(
 *     schema="Broker",
 *     description="A message broker for jobs",
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="The unique name of the broker"
 *     )
 * )
 */
interface BrokerInterface
{
    public function getName(): string;

    public function setup(LoggerInterface $logger = null): void;
}