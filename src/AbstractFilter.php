<?php

namespace Abc\Job;

class AbstractFilter
{
    /**
     * @OA\Parameter(
     *     description="The name of the job",
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
    protected $names;

    /**
     * @OA\Parameter(
     *     description="The external id of the job",
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
    protected $externalIds;


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
    protected $offset;

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
    protected $limit;

    public function __construct()
    {
        $this->names = [];
        $this->externalIds = [];
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
}
