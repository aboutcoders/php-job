<?php

namespace Abc\Job\Processor;

use PHPUnit\Framework\TestCase;

class ProcessorRegistryTest extends TestCase
{
    public function testAddWithProcessorExists()
    {
        $subject = new ProcessorRegistry();
        $subject->add('someJob', $this->createMock(ProcessorInterface::class));

        $this->expectException(\InvalidArgumentException::class);

        $subject->add('someJob', $this->createMock(ProcessorInterface::class));
    }

    public function testGetWithProcessorAdded()
    {
        $processor = $this->createMock(ProcessorInterface::class);
        $subject = new ProcessorRegistry();
        $subject->add('someJob', $processor);
        $this->assertSame($processor, $subject->get('someJob'));
    }

    public function testGetWithProcessorNotAdded()
    {
        $subject = new ProcessorRegistry();
        $this->assertNull($subject->get('someJob'));
    }

    public function testExistsWithProcessorAdded()
    {
        $subject = new ProcessorRegistry();
        $subject->add('someJob', $this->createMock(ProcessorInterface::class));
        $this->assertTrue($subject->exists('someJob'));
    }

    public function testExistsWithJobProcessorAdded()
    {
        $subject = new ProcessorRegistry();
        $this->assertFalse($subject->exists('someJob'));
    }
}
