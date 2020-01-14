<?php

namespace Abc\Job\Tests;

use Abc\Job\JobFilter;
use Abc\Job\Status;
use PHPUnit\Framework\TestCase;

class JobFilterTest extends TestCase
{
    private static $expectedArray = [
        'ids' => 'id1,id2',
        'names' => 'jobA,jobB',
        'status' => Status::CANCELLED.','.Status::FAILED,
        'externalIds' => 'e1,e2',
        'latest' => 'true',
        'offset' => 10,
        'limit' => 20

    ];

    public function testFromQueryString() {
        $filter = JobFilter::fromQueryString('ids=id1,id2&names=jobA,jobB&status=cancelled,failed&externalIds=e1,e2&latest=true&offset=10&limit=20');

        $this->assertEquals(static::$expectedArray, $filter->toQueryParams());
    }

    public function testToQueryParamsWithEmptyFilter()
    {
        $filter = new JobFilter();
        $this->assertEquals([], $filter->toQueryParams());
    }

    public function testToQueryParamsWithFullFilter()
    {
        $filter = new JobFilter();
        $filter->setIds(['id1', 'id2']);
        $filter->setNames(['jobA', 'jobB']);
        $filter->setStatus([Status::CANCELLED, Status::FAILED]);
        $filter->setExternalIds(['e1', 'e2']);
        $filter->setLatest(true);
        $filter->setOffset(10);
        $filter->setLimit(20);

        $this->assertEquals(static::$expectedArray, $filter->toQueryParams());
    }
}
