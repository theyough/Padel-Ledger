<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ScoreProposalInput;
use App\Entity\PadelMatch;
use App\Entity\ScoreProposal;
use App\Entity\ScoreValidation;
use App\Service\MatchMailer;
use App\Service\MatchWorkflow;
use App\Service\ScoreValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CreateScoreProposalProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MatchWorkflow $matchWorkflow,
        private readonly ScoreValidator $scoreValidator,
        private readonly MatchMailer $matchMailer,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PadelMatch
    {
        if (!$data instanceof ScoreProposalInput) {
            throw new BadRequestHttpException('Invalid score proposal payload.');
        }

        $player = $this->matchWorkflow->currentPlayer();
        $match = $this->matchWorkflow->getMatch($uriVariables['id'] ?? 0);
        $this->matchWorkflow->assertPlayerCanAccessMatch($match, $player);

        if (PadelMatch::STATUS_VALIDATED === $match->getStatus()) {
            throw new BadRequestHttpException('This match is already validated.');
        }

        $sets = $this->scoreValidator->normalize($data->sets);
        $this->scoreValidator->winner($sets);

        $oldProposals = $this->entityManager->getRepository(ScoreProposal::class)->findBy([
            'padelMatch' => $match,
            'current' => true,
        ]);
        foreach ($oldProposals as $oldProposal) {
            $oldProposal->setCurrent(false);
        }

        $proposal = new ScoreProposal($match, $player, $sets);
        $validation = new ScoreValidation($proposal, $player, ScoreValidation::DECISION_APPROVED);
        $proposal->addValidation($validation);
        $match->addScoreProposal($proposal);
        $match->setCurrentScoreProposal($proposal)->markPendingValidation();

        $this->entityManager->persist($proposal);
        $this->entityManager->persist($validation);
        $this->entityManager->flush();

        $this->matchMailer->sendValidationInvitations($match, $proposal);

        return $match;
    }
}
