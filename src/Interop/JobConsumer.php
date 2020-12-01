<?php

namespace Abc\Job\Interop;

use Abc\Job\Broker\Config;
use Abc\Job\Processor\Context as JobContext;
use Abc\Job\Processor\ProcessorInterface;
use Abc\Job\Processor\ProcessorRegistry;
use Abc\Job\Processor\Reply;
use Abc\Job\Processor\Result;
use Abc\Job\Status;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * fixme: needs refactoring, broker specific logic should go somewhere else
 */
class JobConsumer implements Processor
{
    /**
     * @var ProcessorRegistry
     */
    private $registry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $jobNames;

    public function __construct(ProcessorRegistry $registry, LoggerInterface $logger)
    {
        $this->registry = $registry;
        $this->logger = $logger;
        $this->jobNames = [];
    }

    /**
     * Limits the jobs that will be processed.
     *
     * If set and a job message is received with a job name that is not in the array,
     * the job will be requeued.
     *
     * @param string[] $names An array of job names
     */
    public function setJobs(array $names)
    {
        $this->jobNames = $names;
    }

    /**
     * @return string[] The names of the jobs that are processed
     */
    public function getJobs(): array
    {
        return $this->jobNames;
    }

    public function process(Message $message, Context $context)
    {
        $logger = $this->logger;
        $jobId = $message->getCorrelationId();

        if (null == $jobId) {
            $this->logger->warning('[JobConsumer] Reject message due to missing correlation id');

            return self::REJECT;
        }

        $jobName = $message->getProperty(Config::NAME, false);
        if (false === $jobName) {
            $this->logger->warning(sprintf('[JobConsumer] Reject message due to missing header "%s"', Config::NAME));

            return self::REJECT;
        }

        $processor = $this->registry->get($jobName);
        if (null == $processor) {
            $this->logger->debug(sprintf('[JobConsumer] Requeue job "%s" due to missing processor', $jobName));

            return self::REQUEUE;
        }

        if (! empty($this->jobNames) && ! in_array($jobName, $this->jobNames)) {
            $this->logger->debug(
                sprintf(
                    '[JobConsumer] Requeue job "%s" because processing is limited to jobNames "%s"',
                    $jobName,
                    json_encode($this->jobNames)
                )
            );

            return self::REQUEUE;
        }

        $sendReplyCallback = function (Reply $reply) use ($message, $context, $logger) {
            $replyMessage = $context->createMessage($reply->toJson());
            $replyMessage->setMessageId(Uuid::v4());
            $replyMessage->setCorrelationId($message->getCorrelationId());
            $replyMessage->setTimestamp(time());

            $replyQueue = $context->createQueue($message->getReplyTo());

            $logger->info(
                sprintf(
                    '[JobConsumer] Send reply of job %s to queue %s: %s',
                    $message->getCorrelationId(),
                    $message->getReplyTo(),
                    $replyMessage->getBody()
                )
            );

            $context->createProducer()
                ->send($replyQueue, $replyMessage)
            ;
        };

        $sendOutputCallback = function (string $output) use ($jobId, $sendReplyCallback) {
            $sendReplyCallback(new Reply($jobId, Status::RUNNING, $output));
        };

        $processorContext = $this->createContext($message, $sendOutputCallback);

        $sendReplyCallback(new Reply($jobId,Status::RUNNING));

        $start = microtime(true);
        $processingTimeCallback = function () use ($start) {
            return microtime(true) - $start;
        };

        $this->logger->debug(
            sprintf(
                '[JobConsumer] Route job %s to processor %s with input %s',
                $jobId,
                get_class($processor),
                $message->getBody()
            )
        );

        try {
            $result = $processor->process($message->getBody(), $processorContext);
        } catch (\Exception $e) {
            $result = $this->onProcessorException(
                $e,
                $sendReplyCallback,
                $processingTimeCallback(),
                $processor,
                $jobId,
                $jobName
            );
        }

        $reply = $this->createReply($jobId, $result, $processingTimeCallback());

        $sendReplyCallback($reply);

        return self::ACK;
    }

    private function createContext(Message $message, \Closure $sendOutputCallback): JobContext
    {
        return new JobContext($message->getCorrelationId(), $sendOutputCallback);
    }

    private function createReply(string $jobId, $result, float $processingTime): Reply
    {
        if (is_a($result, Result::class)) {
            /** @var Result $result */
            return new Reply($jobId, $result->getStatus(), $result->getOutput(), $processingTime);
        }

        if (in_array((string)$result, [Result::COMPLETE, Result::FAILED])) {
            return new Reply($jobId, $result, null, $processingTime);
        }

        throw new \LogicException(sprintf('Status is not supported: %s', $result));
    }

    private function onProcessorException(
        \Exception $exception,
        \Closure $sendReplyCallback,
        float $processingTime,
        ProcessorInterface $processor,
        string $jobId,
        string $jobName
    ): string {

        $this->logger->error(
            sprintf(
                '[JobConsumer] Exception thrown by processor %s when processing job "%s": %s [%s](code: %s) at %s line: %s',
                get_class($processor),
                $jobId,
                $exception->getMessage(),
                get_class($exception),
                $exception->getCode(),
                $exception->getFile(),
                $exception->getLine()
            )
        );

        return Result::FAILED;
    }
}
