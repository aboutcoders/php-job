<?php

namespace Abc\Job\Tests\Model;

use Abc\Job\Model\Job;
use PHPUnit\Framework\TestCase;

class JobTest extends TestCase
{
    public function testCreate()
    {
        $this->assertInstanceOf(TestJob::class, TestJob::create());
    }
}

class TestJob extends Job
{
}
