<?php

namespace Abc\Job;

use MyCLabs\Enum\Enum;

/**
 * @method static Type ASC()
 * @method static Type DESC()
 */
class OrderBy extends Enum
{
    private const ASC = 'ASC';
    private const DESC = 'DESC';
}
