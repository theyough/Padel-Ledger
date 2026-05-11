<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\HealthStatus;

class HealthStatusProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): HealthStatus
    {
        return new HealthStatus();
    }
}
