<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\PadelMatch;
use App\Entity\ScoreProposal;
use App\Service\MatchMailer;
use App\Service\MatchWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class FinishMatchProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MatchWorkflow $matchWorkflow,
        private readonly MatchMailer $matchMailer
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PadelMatch
    {
        $player = $this->matchWorkflow->currentPlayer();
        $match = $this->matchWorkflow->getMatch($uriVariables['id'] ?? 0);
        $this->matchWorkflow->assertPlayerCanAccessMatch($match, $player);

        if ($match->getStatus() === PadelMatch::STATUS_VALIDATED) {
            throw new BadRequestHttpException('This match is already validated.');
        }

        if ($match->getCurrentScoreProposal() instanceof ScoreProposal) {
            $match->markPendingValidation();
        } else {
            $match->markFinished();
        }

        $this->entityManager->flush();
        $this->matchMailer->sendScoreInvitations($match);

        return $match;
    }
}
