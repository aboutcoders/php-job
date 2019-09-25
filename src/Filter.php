<?php

namespace Abc\Job;

class Filter
{
    /**
     * @var string[]
     */
    private $ids;

    /**
     * @var string[]
     */
    private $names;

    /**
     * @var string[]
     */
    private $status;

    /**
     * @var string[]
     */
    private $externalIds;

    public function __construct()
    {
        $this->ids = [];
        $this->names = [];
        $this->status = [];
        $this->externalIds = [];
    }

    public static function fromQueryString(?string $query): Filter
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
