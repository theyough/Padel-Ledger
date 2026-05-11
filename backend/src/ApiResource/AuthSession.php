<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Dto\AuthPayload;
use App\Dto\LoginInput;
use App\State\LoginProcessor;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/auth/login',
            input: LoginInput::class,
            output: AuthPayload::class,
            processor: LoginProcessor::class,
            normalizationContext: ['groups' => ['auth:read', 'player:read']],
            name: 'login'
        ),
    ],
)]
class AuthSession
{
}
