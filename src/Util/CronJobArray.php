<?php

namespace Abc\Job\Util;

use Abc\Job\Model\CronJobInterface;

class CronJobArray
{
    /**
     * @param $cronJobs CronJobInterface[]
     * @return string $json
     */
    public static function toJson(array $cronJobs): string
    {
        $data = [];
        foreach ($cronJobs as $cronJob) {
            $data[] = $cronJob->toArray();
        }

        return json_encode($data);
    }
}
