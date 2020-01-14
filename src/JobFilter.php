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
    private $latest = false;

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
     * @var int|null
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
     * @var int|null
     */
    private $limit;

    public function __construct()
    {
        $this->ids = [];
        $this->names = [];
        $this->status = [];
        $this->externalIds = [];
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

    public function addName(string $name): void
    {
        $this->names[] = $name;
    }

    public function getStatus(): array
    {
        return $this->status;
    }

    public function setStatus(array $status): void
    {
        $this->status = $status;
    }

    public function addStatus(string $status): void
    {
        $this->status[] = $status;
    }

    public function getExternalIds(): array
    {
        return $this->externalIds;
    }

    public function setExternalIds(array $externalIds): void
    {
        $this->externalIds = $externalIds;
    }

    public function addExternalId(string $externalId): void
    {
        $this->externalIds[] = $externalId;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function setOffset(?int $offset): void
    {
        $this->offset = $offset;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(?int $limit): void
    {
        $this->limit = $limit;
    }

    public function isLatest(): bool
    {
        return $this->latest;
    }

    public function setLatest(bool $latest): void
    {
        $this->latest = $latest;
    }

    public static function fromQueryString(?string $query): JobFilter
    {
        parse_str($query, $params);

        $filter = new static();
        if (isset($params['ids'])) {
            $filter->setIds(explode(',', $params['ids']));
        }

        if (isset($params['names'])) {
            $filter->setNames(explode(',', $params['names']));
        }

        if (isset($params['status'])) {
            $filter->setStatus(explode(',', $params['status']));
        }

        if (isset($params['externalIds'])) {
            $filter->setExternalIds(explode(',', $params['externalIds']));
        }

        if (isset($params['latest'])) {
            $filter->setLatest((bool) $params['latest']);
        }

        if (isset($params['offset'])) {
            $filter->setOffset((int) $params['offset']);
        }

        if (isset($params['limit'])) {
            $filter->setLimit((int) $params['limit']);
        }

        return $filter;
    }

    public function toQueryParams(): array
    {
        $map = ['ids', 'names', 'status', 'externalIds'];
        $params = [];
        foreach ($map as $name) {
            if (! empty($this->$name)) {
                $params[$name] = implode(',', $this->$name);
            }
        }

        if ($this->latest) {
            $params['latest'] = 'true';
        }

        if (null !== $this->offset) {
            $params['offset'] = $this->offset;
        }

        if (null !== $this->limit) {
            $params['limit'] = $this->limit;
        }

        return $params;
    }
}
