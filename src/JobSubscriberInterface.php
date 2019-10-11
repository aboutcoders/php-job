<?php

namespace Abc\Job;

interface JobSubscriberInterface
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
     *     'processor' => 'aProcessorName',
     *   ],
     *   [
     *     'job' => 'anotherJobName',
     *     'queue' => 'anotherQueueName',
     *     'replyTo' => 'anotherReplyToQueueName',
     *     'processor' => 'anotherProcessorName',
     *   ]
     * ]
     *
     * @return string|array
     */
    public static function getSubscribedJob();
}
