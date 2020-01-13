<?php

namespace Abc\Job;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     description="The filter of results"
 * )
 */
class JobFilter
{
    /**
     * @OA\Parameter(
     *     description="The id of the job to get",
     *     in="query",
     *     name="ids",
     *     required=false,
     *     style="simple",
     *     explode="false",
     *     @OA\Schema(
     *         type="array",
     *         @OA\Items(
     *             type="string",
     *             format="uuid"
     *         )
     *     )
     * )
     *
     * @var string[]
     */
    private $ids;

    /**
     * @OA\Parameter(
     *     description="The names of the job to get",
     *     in="query",
     *     name="names",
     *     required=false,
     *     style="simple",
     *     explode="false",
     *     @OA\Schema(
     *         type="array",
     *         @OA\Items(
     *             type="string"
     *         )
     *     )
     * )
     *
     * @var string[]
     */
    private $names;

    /**
     * @OA\Parameter(
     *     description="The status of the job to get",
     *     in="query",
     *     name="status",
     *     required=false,
     *     style="simple",
     *     explode="false",
     *     @OA\Schema(
     *         type="array",
     *         @OA\Items(
     *             type="string",
     *             enum={"waiting", "scheduled", "running", "complete", "failed", "cancelled"}
     *         )
     *     )
     * )
     *
     * @var string[]
     */
    private $status;

    /**
     * @OA\Parameter(
     *     description="The externalIds of the job to get",
     *     in="query",
     *     name="externalIds",
     *     required=false,
     *     style="simple",
     *     explode="false",
     *     @OA\Schema(
     *         type="array",
     *         @OA\Items(
     *             type="string",
     *             format="uuid"
     *         )
     *     )
     * )
     *
     * @var string[]
     */
    private $externalIds;


    /**
     * @OA\Parameter(
     *     description="If true, the endpoint only returns the latest job",
     *     in="query",
     *     name="latest",
     *     required=false,
     *     @OA\Schema(
     *         type="boolean"
     *     )
     * )
     *
     * @var bool
     */
    private $latest;

    /**
     * @OA\Parameter(
     *     description="The result offset",
     *     in="query",
     *     name="offset",
     *     required=false,
     *     @OA\Schema(
     *         type="integer"
     *     )
     * )
     *
     * @var int
     */
    private $offset;

    /**
     * @OA\Parameter(
     *     description="The result limit",
     *     in="query",
     *     name="limit",
     *     required=false,
     *     @OA\Schema(
     *         type="integer"
     *     )
     * )
     *
     * @var int
     */
    private $limit;


    public function __construct()
    {
        $this->ids = [];
        $this->names = [];
        $this->status = [];
        $this->externalIds = [];
    }

    public static function fromQueryString(?string $query): JobFilter
    {
        parse_str($query, $params);

        $filter = new static();
        if (isset($params['id'])) {
            $filter->setIds(static::toArray($params['id']));
        }

        if (isset($params['name'])) {
            $filter->setNames(static::toArray($params['name']));
        }

        if (isset($params['status'])) {
            $filter->setStatus(static::toArray($params['status']));
        }

        if (isset($params['externalId'])) {
            $filter->setExternalIds(static::toArray($params['externalId']));
        }

        return $filter;
    }

    public function toQueryParams(): array
    {
        $map = ['ids' => 'id', 'names' => 'name', 'status' => 'status', 'externalIds' => 'externalId'];
        $params = [];
        foreach ($map as $classKey => $paramKey) {
            if (! empty($this->$classKey)) {
                $params[$paramKey] = (1 == count($this->$classKey)) ? array_pop($this->$classKey) : $this->$classKey;
            }
        }

        return $params;
    }

    public function getIds(): array
    {
        return $this->ids;
    }

    public function setIds(array $ids): void
    {
        $this->ids = $ids;
    }

    public function getNames(): array
    {
        return $this->names;
    }

    public function setNames(array $names): void
    {
        $this->names = $names;
    }

    public function getStatus(): array
    {
        return $this->status;
    }

    public function setStatus(array $status): void
    {
        $this->status = $status;
    }

    public function getExternalIds(): array
    {
        return $this->externalIds;
    }

    public function setExternalIds(array $externalIds): void
    {
        $this->externalIds = $externalIds;
    }

    private static function toArray($param): array
    {
        if (! is_array($param)) {
            return [$param];
        }

        return $param;
    }
}
