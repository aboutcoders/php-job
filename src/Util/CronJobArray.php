<?php

namespace Abc\Job\Util;

use Abc\Job\Model\CronJobInterface;

class CronJobArray
{
    /**
     * @param $scheduledJobs CronJobInterface[]
     * @return string $json
     */
    public static function toJson(array $scheduledJobs): string
    {
        $data = [];
        foreach ($scheduledJobs as $scheduledJob) {
            $data[] = $scheduledJob->toArray();
        }

        return json_encode($data);
    }
}
