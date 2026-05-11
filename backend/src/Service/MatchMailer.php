<?php

namespace App\Service;

use App\Entity\PadelMatch;
use App\Entity\Player;
use App\Entity\ScoreProposal;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MatchMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        #[Autowire('%env(FRONTEND_URL)%')]
        private readonly string $frontendUrl,
    ) {
    }

    public function sendScoreInvitations(PadelMatch $match): void
    {
        foreach ($match->getPlayers() as $player) {
            $this->send(
                $player,
                'Match score requested',
                \sprintf(
                    "Hello %s,\n\nMatch #%d is finished. You can enter or validate the score here:\n%s/matches/%d\n",
                    $player->getFirstName(),
                    $match->getId(),
                    $this->frontendUrl,
                    $match->getId()
                )
            );
        }
    }

    public function sendValidationInvitations(PadelMatch $match, ScoreProposal $proposal): void
    {
        foreach ($match->getPlayers() as $player) {
            if ($player->getId() === $proposal->getProposedBy()->getId()) {
                continue;
            }

            $this->send(
                $player,
                'Match score validation requested',
                \sprintf(
                    "Hello %s,\n\nA score has been proposed for match #%d. Please validate it or submit a correction:\n%s/matches/%d\n",
                    $player->getFirstName(),
                    $match->getId(),
                    $this->frontendUrl,
                    $match->getId()
                )
            );
        }
    }

    private function send(Player $player, string $subject, string $text): void
    {
        $email = (new Email())
            ->from('club@padel.local')
            ->to($player->getEmail())
            ->subject($subject)
            ->text($text);

        $this->mailer->send($email);
    }
}
