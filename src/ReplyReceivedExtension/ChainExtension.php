<?php

namespace Abc\Job\ReplyReceivedExtension;

use Abc\Job\ReplyReceivedExtensionInterface;
use Abc\Job\Result;

class ChainExtension implements ReplyReceivedExtensionInterface
{
    /**
     * @var ReplyReceivedExtensionInterface[]
     */
    private $replyReceivedExtensions;

    public function __construct(array $extensions)
    {
        $this->replyReceivedExtensions = [];

        array_walk(
            $extensions,
            function ($extension) {
                $extensionValid = false;
                if ($extension instanceof ReplyReceivedExtensionInterface) {
                    $this->replyReceivedExtensions[] = $extension;

                    $extensionValid = true;
                }

                if (false == $extensionValid) {
                    throw new \LogicException(sprintf('Invalid extension given %s', get_class($extension)));
                }
            }
        );
    }

    public function onReplyReceived(Result $result)
    {
        foreach ($this->replyReceivedExtensions as $extension) {
            $extension->onReplyReceived($result);
        }
    }
}
