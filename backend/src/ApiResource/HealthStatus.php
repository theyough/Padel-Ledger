<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\HealthStatusProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/health',
            uriVariables: [],
            read: false,
            provider: HealthStatusProvider::class,
            name: 'health'
        ),
    ],
)]
class HealthStatus
{
    public function __construct(public string $status = 'ok')
    {
    }
}
