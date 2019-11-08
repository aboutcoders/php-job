<?php

namespace Abc\Job\Tests;

use Abc\Job\JobFilter;
use Abc\Job\Status;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testToQueryParams() {
        $filter = new JobFilter();
        $filter->setNames(['foo']);
        $filter->setIds(['id1', 'id2']);
        $filter->setStatus([Status::CANCELLED]);
        $filter->setExternalIds(['e1', 'e2']);

        $this->assertEquals([
            'name' => 'foo',
            'id' => ['id1', 'id2'],
            'status' => Status::CANCELLED,
            'externalId' => ['e1', 'e2']

        ], $filter->toQueryParams());
    }
}
