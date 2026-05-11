<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\RejectScoreInput;
use App\Entity\PadelMatch;
use App\Entity\ScoreValidation;
use App\Service\MatchWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RejectCurrentScoreProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MatchWorkflow $matchWorkflow,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PadelMatch
    {
        if (!$data instanceof RejectScoreInput) {
            throw new BadRequestHttpException('Invalid score rejection payload.');
        }

        $player = $this->matchWorkflow->currentPlayer();
        $match = $this->matchWorkflow->getMatch($uriVariables['id'] ?? 0);
        $this->matchWorkflow->assertPlayerCanAccessMatch($match, $player);

        if (PadelMatch::STATUS_VALIDATED === $match->getStatus()) {
            throw new BadRequestHttpException('This match is already validated.');
        }

        $proposal = $this->matchWorkflow->getCurrentProposal($match);
        $this->matchWorkflow->upsertValidation(
            $proposal,
            $player,
            ScoreValidation::DECISION_REJECTED,
            $data->comment
        );
        $this->entityManager->flush();

        return $match;
    }
}
