<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\PadelMatch;
use App\Service\MatchWorkflow;
use Doctrine\ORM\EntityManagerInterface;

class MatchCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MatchWorkflow $matchWorkflow
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $player = $this->matchWorkflow->currentPlayer();

        return $this->entityManager->createQueryBuilder()
            ->select('m')
            ->from(PadelMatch::class, 'm')
            ->where('m.teamAPlayer1 = :player')
            ->orWhere('m.teamAPlayer2 = :player')
            ->orWhere('m.teamBPlayer1 = :player')
            ->orWhere('m.teamBPlayer2 = :player')
            ->setParameter('player', $player)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
