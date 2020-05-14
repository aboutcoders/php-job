<?php

namespace Abc\Job;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     description="The filter of results"
 * )
 */
class JobFilter extends AbstractFilter
{
    /**
     *
     *
     * @var string[]
     */
    private $ids;

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
