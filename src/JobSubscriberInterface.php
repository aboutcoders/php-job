<?php

namespace Abc\Job;

interface JobSubscriberInterface
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
     *     'job' => 'aSubscribedJob',
     *     'processor' => 'aProcessorName',
     *   ],
     *   [
     *     'job' => 'aSubscribedJob',
     *     'processor' => 'aProcessorName',
     *   ]
     * ]
     *
     * @return string|array
     */
    public static function getSubscribedJob();
}
