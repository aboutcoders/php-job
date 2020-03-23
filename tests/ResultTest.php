<?php

namespace Abc\Job\Tests;

use Abc\Job\Result;
use Abc\Job\Tests\DataProvider\JobProvider;
use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{
    public function testSerialization()
    {
        $sequence = JobProvider::createSequence('myExternalId');
        $result = new Result($sequence);

        $json = $result->toJson();
        $object = json_decode($json);

        $this->assertEquals('Sequence', $object->type);
        $this->assertEquals($sequence->getId(), $object->id);
        $this->assertEquals($sequence->getStatus(), $object->status);
        $this->assertEquals($sequence->getName(), $object->name);
        $this->assertEquals($sequence->getInput(), $object->input);
        $this->assertEquals($sequence->getOutput(), $object->output);
        $this->assertEquals($sequence->isAllowFailure(), $object->allowFailure);
        $this->assertEquals($sequence->getExternalId(), $object->externalId);
        $this->assertEquals($sequence->getRestarts(), $object->restarts);
        $this->assertEquals($sequence->getProcessingTime(), $object->processingTime);
        $this->assertEquals($sequence->getCompletedAt()->format('c'), $object->completed);
        $this->assertEquals($sequence->getUpdatedAt()->format('c'), $object->updated);
        $this->assertEquals($sequence->getCreatedAt()->format('c'), $object->created);

        $this->assertEquals('Job', $object->children[0]->type);
        $this->assertEquals($sequence->getChildren()[0]->getId(), $object->children[0]->id);
        $this->assertEquals($sequence->getChildren()[0]->getStatus(), $object->children[0]->status);
        $this->assertEquals($sequence->getChildren()[0]->getName(), $object->children[0]->name);
        $this->assertEquals($sequence->getChildren()[0]->getInput(), $object->children[0]->input);
        $this->assertEquals($sequence->getChildren()[0]->getOutput(), $object->children[0]->output);
        $this->assertEquals($sequence->getChildren()[0]->isAllowFailure(), $object->children[0]->allowFailure);
        $this->assertEquals($sequence->getChildren()[0]->getExternalId(), $object->children[0]->externalId);
        $this->assertEquals($sequence->getChildren()[0]->getRestarts(), $object->children[0]->restarts);
        $this->assertEquals($sequence->getChildren()[0]->getProcessingTime(), $object->children[0]->processingTime);
        $this->assertEquals($sequence->getChildren()[0]->getCompletedAt()->format('c'), $object->children[0]->completed);
        $this->assertEquals($sequence->getChildren()[0]->getUpdatedAt()->format('c'), $object->children[0]->updated);
        $this->assertEquals($sequence->getChildren()[0]->getCreatedAt()->format('c'), $object->children[0]->created);

        $decodedResult = Result::fromJson($json);
        $this->assertEquals($result, $decodedResult);
    }
}
