<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\AuthPayload;
use App\Dto\RegisterPlayerInput;
use App\Entity\Player;
use App\Service\QuestionnaireLevelEstimator;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterPlayerProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly JWTTokenManagerInterface $jwtTokenManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly QuestionnaireLevelEstimator $levelEstimator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AuthPayload
    {
        if (!$data instanceof RegisterPlayerInput) {
            throw new BadRequestHttpException('Invalid registration payload.');
        }

        $email = strtolower(trim($data->email));
        if ('' === $email || !filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            throw new BadRequestHttpException('Invalid email.');
        }

        if (\strlen($data->password) < 8) {
            throw new BadRequestHttpException('The password must contain at least 8 characters.');
        }

        if ($this->entityManager->getRepository(Player::class)->findOneBy(['email' => $email])) {
            throw new ConflictHttpException('A player already exists with this email.');
        }

        $questionnaire = $data->questionnaire;
        $level = $questionnaire ? $this->levelEstimator->estimate($questionnaire) : 1;
        $player = (new Player())
            ->setEmail($email)
            ->setFirstName($data->firstName)
            ->setLastName($data->lastName)
            ->setQuestionnaireAnswers($questionnaire)
            ->setRating($level * 100.0);
        $player->setPasswordHash($this->passwordHasher->hashPassword($player, $data->password));

        $this->entityManager->persist($player);
        $this->entityManager->flush();

        return new AuthPayload($this->jwtTokenManager->create($player), $player);
    }
}
