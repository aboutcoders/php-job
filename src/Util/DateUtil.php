<?php

namespace Abc\Job\Util;

class DateUtil
{
    public static function createDate(int $timestamp): \DateTime
    {
        return new \DateTime('@'.$timestamp, new \DateTimeZone('UTC'));
    }
}
