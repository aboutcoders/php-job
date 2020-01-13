<?php

namespace Abc\Job;

interface Status
{
    /**
     * The job is waiting to be scheduled
     */
    const WAITING = 'waiting';

    /**
     * The job is scheduled in the queue
     */
    const SCHEDULED = 'scheduled';

    /**
     * The job is running
     */
    const RUNNING = 'running';

    /**
     * The job was successfully processed
     */
    const COMPLETE = 'complete';

    /**
     * The job was not successfully processed
     */
    const FAILED = 'failed';

    /**
     * The job was cancelled
     */
    const CANCELLED = 'cancelled';
}
