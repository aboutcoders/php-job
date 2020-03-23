<?php

namespace Abc\Job\Broker;

class Registry implements RegistryInterface
{
    /**
     * @var BrokerInterface[]
     */
    private $elements = [];

    public function add(string $name, BrokerInterface $broker): self
    {
        if (isset($this->elements[$name])) {
            throw new \InvalidArgumentException(sprintf('A broker with name "%s" is already registered', $name));
        }

        $this->elements[$name] = $broker;

        return $this;
    }

    public function exists(string $name): bool
    {
        return isset($this->elements[$name]);
    }

    public function get(string $name): ?BrokerInterface
    {
        return $this->elements[$name] ?? null;
    }
}