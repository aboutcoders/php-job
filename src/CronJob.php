<?php

namespace Abc\Job;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     description="A job that is processed according to cronjob expression",
 *     schema="CronJob",
 *     properties={
 *         @OA\Property(
 *             property="id",
 *             type="string",
 *             format="uuid",
 *             readOnly="true",
 *         ),
 *         @OA\Property(
 *             property="schedule",
 *             type="string",
 *             description="The schedule in Cron format, see https://en.wikipedia.org/wiki/Cron."
 *         ),
 *         @OA\Property(
 *             property="type",
 *             ref="#/components/schemas/Job/properties/type",
 *         ),
 *         @OA\Property(
 *             property="name",
 *             ref="#/components/schemas/Job/properties/name",
 *         ),
 *         @OA\Property(
 *             property="input",
 *             ref="#/components/schemas/Job/properties/input",
 *         ),
 *         @OA\Property(
 *             property="externalId",
 *             ref="#/components/schemas/Job/properties/externalId",
 *         ),
 *         @OA\Property(
 *             property="children",
 *             ref="#/components/schemas/Job/properties/children",
 *         ),
 *         @OA\Property(
 *             property="updated",
 *             description="The datetime the cronjob was updated",
 *             type="string",
 *             format="date-time",
 *             readOnly=true
 *         ),
 *         @OA\Property(
 *             property="created",
 *             description="The datetime the cronjob was created",
 *             type="string",
 *             format="date-time",
 *             readOnly=true
 *         )
 *     }
 * )
 */
interface CronJob
{
    public function getId(): ?string;

    public function getSchedule(): string;

    public function setSchedule(string $expression): void;

    public function getJob(): Job;

    public function setJob(Job $job): void;

    public function getCreated(): ?\DateTime;

    public function getUpdated(): ?\DateTime;
}
