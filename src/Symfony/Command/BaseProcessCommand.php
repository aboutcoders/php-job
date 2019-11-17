<?php

namespace Abc\Job\Symfony\Command;

use Symfony\Component\Console\Command\Command;
use Enqueue\Symfony\Consumption\ConfigurableConsumeCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class BaseProcessCommand extends Command
{
    /**
     * @var ConfigurableConsumeCommand
     */
    protected $consumeCommand;

    public function __construct(ConfigurableConsumeCommand $consumeCommand)
    {
        $this->consumeCommand = $consumeCommand;

        parent::__construct(static::$defaultName);
    }

    protected function configureOptions()
    {
        foreach ($this->consumeCommand->getDefinition()->getOptions() as $option) {
            $this->addOption($option->getName(), $option->getShortcut(), $this->getOptionMode($option), $option->getDescription(), $option->getDefault());
        }
    }

    protected function buildParameters(InputInterface $input, $parameters = []): array
    {
        foreach ($this->consumeCommand->getDefinition()->getOptions() as $inputOption) {
            if ($input->hasOption($inputOption->getName()) && $input->getOption($inputOption->getName()) != null) {
                $parameters['--'.$inputOption->getName()] = $input->getOption($inputOption->getName());
            }
        }

        return $parameters;
    }

    private function getOptionMode(InputOption $option): ?int
    {
        $mode = InputOption::VALUE_NONE;

        if ($option->isValueRequired()) {
            $mode = InputOption::VALUE_REQUIRED;
        }

        if ($option->isValueOptional()) {
            $mode = InputOption::VALUE_OPTIONAL;
        }

        if ($option->isArray()) {
            $mode = InputOption::VALUE_IS_ARRAY;
        }

        return $mode;
    }
}
