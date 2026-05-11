<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\PadelMatch;
use App\Entity\ScoreValidation;
use App\Service\LevelCalculator;
use App\Service\MatchWorkflow;
use Doctrine\ORM\EntityManagerInterface;

class ApproveCurrentScoreProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MatchWorkflow $matchWorkflow,
        private readonly LevelCalculator $levelCalculator
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PadelMatch
    {
        $player = $this->matchWorkflow->currentPlayer();
        $match = $this->matchWorkflow->getMatch($uriVariables['id'] ?? 0);
        $this->matchWorkflow->assertPlayerCanAccessMatch($match, $player);

        if ($match->getStatus() === PadelMatch::STATUS_VALIDATED) {
            return $match;
        }

        $proposal = $this->matchWorkflow->getCurrentProposal($match);
        $this->matchWorkflow->upsertValidation($proposal, $player, ScoreValidation::DECISION_APPROVED);
        $this->entityManager->flush();

        if ($this->matchWorkflow->allPlayersApproved($match, $proposal)) {
            $this->levelCalculator->applyValidatedMatch($match, $proposal);
            $match->markValidated();
            $this->entityManager->flush();
        }

        return $match;
    }
}
