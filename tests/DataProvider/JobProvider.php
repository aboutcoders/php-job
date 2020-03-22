<?php

namespace Abc\Job\Tests\DataProvider;

use Abc\Job\Model\Job;
use Abc\Job\Status;
use Abc\Job\Type;

class JobProvider
{
    public static function createJob(string $externalId = null): Job
    {
        $job = new Job();
        $job->setType(Type::JOB());
        $job->setId('JobId');
        $job->setName('JobName');
        $job->setStatus(Status::COMPLETE);
        $job->setInput('someInput');
        $job->setOutput('someOutput');
        $job->setProcessingTime(0.5);
        $job->setExternalId($externalId);
        $job->setRestarts(10);
        $job->setCreatedAt(new \DateTime('@10'));
        $job->setUpdatedAt(new \DateTime('@100'));
        $job->setCompletedAt(new \DateTime('@1000'));

        return $job;
    }

    public static function createSequence(string $externalId = null): Job
    {
        $sequence = new Job();
        $sequence->setType(Type::SEQUENCE());
        $sequence->setId('SequenceId');
        $sequence->setName('JobName');
        $sequence->setStatus(Status::COMPLETE);
        $sequence->setProcessingTime(1.0);
        $sequence->setExternalId($externalId);
        $sequence->setRestarts(10);
        $sequence->setCreatedAt(new \DateTime('@10'));
        $sequence->setUpdatedAt(new \DateTime('@100'));
        $sequence->setCompletedAt(new \DateTime('@1000'));
        $sequence->addChild(static::createJob());

        return $sequence;
    }
}
