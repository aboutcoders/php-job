<?php

namespace Abc\Job\Util;

use Abc\Job\CronJob;
use Abc\Job\Model\CronJobInterface;

class CronJobArray
{
    /**
     * @param string $json
     * @return CronJob[]
     */
    public static function fromJson(string $json): array
    {
        $cronJobs = [];
        foreach (json_decode($json, true) as $data) {
            $cronJobs[] = \Abc\Job\Model\CronJob::fromArray($data);
        }

        return $cronJobs;
    }

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
