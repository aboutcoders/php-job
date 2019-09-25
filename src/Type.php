<?php

namespace Abc\Job;

use MyCLabs\Enum\Enum;

/**
 * @method static Type JOB()
 * @method static Type BATCH()
 * @method static Type SEQUENCE()
 */
class Type extends Enum
{
    private const JOB = 'Job';
    private const BATCH = 'Batch';
    private const SEQUENCE = 'Sequence';
}
