<?php

namespace Abc\Job\Broker;

interface RegistryInterface
{
    public function exists(string $name): bool;

    public function get(string $name): ?BrokerInterface;
}