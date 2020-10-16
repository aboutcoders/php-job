<?php

namespace Abc\Job\PreSaveExtension;

use Abc\Job\Model\JobInterface;

interface PreSaveExtensionInterface
{
    public function onPreSave(JobInterface $job): void;
}
