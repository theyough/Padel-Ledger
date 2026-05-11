<?php

namespace App\Service;

use App\Entity\PadelMatch;
use App\Entity\Player;
use App\Entity\ScoreProposal;
use App\Entity\ScoreValidation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MatchWorkflow
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security
    ) {
    }

    public function currentPlayer(): Player
    {
        $user = $this->security->getUser();
        if (!$user instanceof Player) {
            throw new AccessDeniedHttpException('Authentication required.');
        }

        return $user;
    }

    public function getMatch(int|string $id): PadelMatch
    {
        $match = $this->entityManager->getRepository(PadelMatch::class)->find((int) $id);
        if (!$match instanceof PadelMatch) {
            throw new NotFoundHttpException('Match was not found.');
        }

        return $match;
    }

    public function assertPlayerCanAccessMatch(PadelMatch $match, Player $player): void
    {
        if (!$match->hasPlayer($player)) {
            throw new AccessDeniedHttpException('Access denied for this match.');
        }
    }

    public function getCurrentProposal(PadelMatch $match): ScoreProposal
    {
        $proposal = $match->getCurrentScoreProposal();
        if (!$proposal instanceof ScoreProposal) {
            throw new BadRequestHttpException('There is no current score to validate.');
        }

        return $proposal;
    }

    public function upsertValidation(ScoreProposal $proposal, Player $player, string $decision, ?string $comment = null): ScoreValidation
    {
        $validation = $this->entityManager->getRepository(ScoreValidation::class)->findOneBy([
            'scoreProposal' => $proposal,
            'player' => $player,
        ]);

        if (!$validation instanceof ScoreValidation) {
            $validation = new ScoreValidation($proposal, $player, $decision, $comment);
            $proposal->addValidation($validation);
            $this->entityManager->persist($validation);

            return $validation;
        }

        $validation->setDecision($decision)->setComment($comment);

        return $validation;
    }

    public function allPlayersApproved(PadelMatch $match, ScoreProposal $proposal): bool
    {
        foreach ($match->getPlayers() as $player) {
            $validation = $this->entityManager->getRepository(ScoreValidation::class)->findOneBy([
                'scoreProposal' => $proposal,
                'player' => $player,
            ]);

            if (!$validation instanceof ScoreValidation || $validation->getDecision() !== ScoreValidation::DECISION_APPROVED) {
                return false;
            }
        }

        return true;
    }
}
