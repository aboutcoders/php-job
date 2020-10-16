<?php

namespace Abc\Job\PreSaveExtension;

use Abc\Job\Model\JobInterface;

class ChainExtension implements PreSaveExtensionInterface
{
    /**
     * @var PreSaveExtensionInterface[]
     */
    private $replyReceivedExtensions;

    public function __construct(array $extensions)
    {
        $this->replyReceivedExtensions = [];

        array_walk(
            $extensions,
            function ($extension) {
                $extensionValid = false;
                if ($extension instanceof PreSaveExtensionInterface) {
                    $this->replyReceivedExtensions[] = $extension;

                    $extensionValid = true;
                }

                if (false == $extensionValid) {
                    throw new \LogicException(sprintf('Invalid extension given %s', get_class($extension)));
                }
            }
        );
    }

    public function onPreSave(JobInterface $job): void
    {
        foreach ($this->replyReceivedExtensions as $extension) {
            $extension->onPreSave($job);
        }
    }
}
