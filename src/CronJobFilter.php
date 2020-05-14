<?php

namespace Abc\Job;

class CronJobFilter extends AbstractFilter
{
    /**
     * @OA\Parameter(
     *     parameter="cronjob-ids"
     *     description="The id of the cronjob to get",
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


    public function __construct()
    {
        parent::__construct();

        $this->ids = [];
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


    public static function fromQueryString(?string $query): CronJobFilter
    {
        parse_str($query, $params);

        $filter = new static();
        if (isset($params['ids'])) {
            $filter->setIds(explode(',', $params['ids']));
        }

        if (isset($params['names'])) {
            $filter->setNames(explode(',', $params['names']));
        }

        if (isset($params['externalIds'])) {
            $filter->setExternalIds(explode(',', $params['externalIds']));
        }

        if (isset($params['offset'])) {
            $filter->setOffset((int)$params['offset']);
        }

        if (isset($params['limit'])) {
            $filter->setLimit((int)$params['limit']);
        }

        return $filter;
    }

    public function toQueryParams(): array
    {
        $map = ['ids', 'names', 'externalIds'];
        $params = [];
        foreach ($map as $name) {
            if (!empty($this->$name)) {
                $params[$name] = implode(',', $this->$name);
            }
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
