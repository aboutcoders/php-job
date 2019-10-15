<?php

namespace Abc\Job;

interface RouteProviderInterface
{
    /**
     * The result may be either:
     *
     * [
     *   'job' => 'aJobName',
     *   'queue' => 'aQueueName',
     *   'replyTo' => 'aReplyToQueueName',
     * ],
     *
     * or
     *
     * [
     *   [
     *     'job' => 'aJobName',
     *     'queue' => 'aQueueName',
     *     'replyTo' => 'aReplyToQueueName',
     *   ],
     *   [
     *     'job' => 'anotherJobName',
     *     'queue' => 'anotherQueueName',
     *     'replyTo' => 'anotherReplyToQueueName',
     *   ]
     * ]
     *
     * @return string|array
     */
    public static function getRoutes();
}
