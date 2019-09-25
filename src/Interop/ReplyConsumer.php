<?php

namespace Abc\Job\Interop;

use Abc\Job\NotFoundException;
use Abc\Job\Processor\Reply;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Abc\Job\ReplyProcessor;
use Psr\Log\LoggerInterface;

class ReplyConsumer implements Processor
{
    /**
     * @var ReplyProcessor
     */
    private $replyProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ReplyProcessor $replyProcessor, LoggerInterface $logger)
    {
        $this->replyProcessor = $replyProcessor;
        $this->logger = $logger;
    }

    public function process(Message $message, Context $context)
    {
        $this->logger->debug(sprintf('[ReplyConsumer] Process reply for job %s %s', $message->getCorrelationId(), $message->getBody()));

        try {
            $reply = Reply::fromJson($message->getBody());
            $this->replyProcessor->process($message->getCorrelationId(), $reply);
        } catch (NotFoundException $e) {
            $this->logger->error(sprintf('[ReplyConsumer] %s', $e->getMessage()));
            $this->logger->info(sprintf('[ReplyConsumer] Reject reply for job %s %s', $message->getCorrelationId(), $message->getBody()));

            return self::REJECT;
        }

        return self::ACK;
    }
}
