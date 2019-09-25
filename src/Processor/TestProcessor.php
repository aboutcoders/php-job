<?php

namespace Abc\Job\Processor;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * This processor is only for testing purposes.
 */
class TestProcessor implements ProcessorInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * Expects a JSON encoded object, following properties are processed:
     *
     * sendOutput: A string that will be send back while processing
     * exception: An exception message that will be thrown
     * sleep: number of seconds to sleep
     * output: some output that will be sent back
     * fail: boolean, whether to terminate with status "failed"
     *
     * {@inheritdoc}
     * @throws \Exception
     */
    public function process(?string $input, Context $context)
    {
        $this->logger->notice(sprintf('[TestProcessor] Process with input %s', $input));

        $parameters = json_decode($input);
        $output = null;

        if (isset($parameters->sendOutput)) {

            $context->sendOutput(json_encode($parameters->output));
        }

        if (isset($parameters->exception)) {

            throw new \Exception((string) $parameters->exception);
        }

        if (isset($parameters->sleep)) {

            $this->logger->notice(sprintf('[TestProcessor] Sleep %s seconds', $parameters->sleep));

            sleep((int) $parameters->sleep);
        }

        if (isset($parameters->output)) {

            $this->logger->notice(sprintf('[TestProcessor] Complete with output %s', $parameters->output));

            $output = is_string($parameters->output) ? $parameters->output : json_encode($parameters->output);
        }

        return new Result(isset($parameters->fail) ? self::FAILED : self::COMPLETE, $output);
    }
}
