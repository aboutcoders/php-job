<?php

namespace Abc\Job\Processor;

use Abc\Job\Status;

interface ProcessorInterface
{
    /**
     * Use this constant when the job is processed successfully.
     */
    const COMPLETE = Status::COMPLETE;

    /**
     * Use this constant when the job is processed with an error.
     */
    const FAILED = Status::FAILED;

    /**
     * @param null|string $input
     * @param Context $context
     * @return string|Result|object with __toString method implemented
     */
    public function process(?string $input, Context $context);
}
