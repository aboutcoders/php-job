<?php

namespace Abc\Job;

interface ReplyReceivedExtensionInterface
{
    public function onReplyReceived(Result $result): void;
}
