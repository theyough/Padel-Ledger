<?php

namespace App\Service;

use App\Entity\PadelMatch;
use App\Entity\Player;
use App\Entity\ScoreProposal;

class LevelCalculator
{
    public function __construct(private readonly ScoreValidator $scoreValidator)
    {
    }

    public function applyValidatedMatch(PadelMatch $match, ScoreProposal $scoreProposal): array
    {
        $sets = $scoreProposal->getSets();
        $winner = $this->scoreValidator->winner($sets);
        $teamA = $match->getTeamAPlayers();
        $teamB = $match->getTeamBPlayers();
        $teamARating = $this->averageRating($teamA);
        $teamBRating = $this->averageRating($teamB);

        $expectedA = 1.0 / (1.0 + 10 ** (($teamBRating - $teamARating) / 400.0));
        $actualA = $winner === 'A' ? 1.0 : 0.0;
        $marginFactor = 1.0 + min(0.5, $this->scoreValidator->margin($sets) / 36.0);
        $teamDeltaA = 32.0 * $marginFactor * ($actualA - $expectedA);
        $changes = [];

        foreach ($teamA as $player) {
            $changes[] = $this->applyPlayerDelta($player, $teamBRating, $teamDeltaA);
        }

        foreach ($teamB as $player) {
            $changes[] = $this->applyPlayerDelta($player, $teamARating, -$teamDeltaA);
        }

        return $changes;
    }

    /**
     * Lower-rated players gain slightly more against strong opponents, and favorites lose more when upset.
     */
    private function applyPlayerDelta(Player $player, float $opponentAverageRating, float $teamDelta): array
    {
        $oldRating = $player->getRating();
        $oldLevel = $player->getLevel();
        $directionalGap = $teamDelta >= 0
            ? $opponentAverageRating - $oldRating
            : $oldRating - $opponentAverageRating;
        $personalFactor = max(0.75, min(1.25, 1.0 + ($directionalGap / 800.0)));
        $experienceFactor = $player->getMatchCount() < 10 ? 1.15 : 1.0;
        $delta = $teamDelta * $personalFactor * $experienceFactor;

        $player->setRating($oldRating + $delta);
        $player->incrementMatchCount();

        return [
            'playerId' => $player->getId(),
            'oldRating' => round($oldRating, 1),
            'newRating' => round($player->getRating(), 1),
            'oldLevel' => $oldLevel,
            'newLevel' => $player->getLevel(),
            'delta' => round($player->getRating() - $oldRating, 1),
        ];
    }

    /**
     * @param Player[] $players
     */
    private function averageRating(array $players): float
    {
        return array_sum(array_map(fn (Player $player) => $player->getRating(), $players)) / count($players);
    }
}
