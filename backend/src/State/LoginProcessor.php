<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\AuthPayload;
use App\Dto\LoginInput;
use App\Entity\Player;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly JWTTokenManagerInterface $jwtTokenManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AuthPayload
    {
        if (!$data instanceof LoginInput) {
            throw new UnauthorizedHttpException('Bearer', 'Invalid credentials.');
        }

        $email = strtolower(trim($data->email));
        $player = $this->entityManager->getRepository(Player::class)->findOneBy(['email' => $email]);

        if (!$player instanceof Player || !$this->passwordHasher->isPasswordValid($player, $data->password)) {
            throw new UnauthorizedHttpException('Bearer', 'Invalid credentials.');
        }

        return new AuthPayload($this->jwtTokenManager->create($player), $player);
    }
}
