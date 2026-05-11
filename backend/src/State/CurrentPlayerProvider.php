<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Player;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CurrentPlayerProvider implements ProviderInterface
{
    public function __construct(private readonly Security $security)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Player
    {
        $user = $this->security->getUser();
        if (!$user instanceof Player) {
            throw new AccessDeniedHttpException('Authentication required.');
        }

        return $user;
    }
}
