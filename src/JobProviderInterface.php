<?php

namespace Abc\Job;

interface JobProviderInterface
{
    /**
     * The result maybe either:
     *
     * 'aJobName'
     *
     * or
     *
     * ['aJobName', 'anotherJobName']
     *
     * or
     *
     * [
     *   [
     *     'name' => 'aJobName',
     *     'queue' => 'a_queue_name',
     *   ],
     *   [
     *     'name' => 'anotherJobName',
     *     'queue' => 'another_queue_name',
     *     'replyTo' => 'another_reply_to',
     *   ],
     * ]
     *
     * @return string|array
     */
    public static function getJobs();
}
