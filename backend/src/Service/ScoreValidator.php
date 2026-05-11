<?php

namespace App\Service;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ScoreValidator
{
    public function normalize(array $sets): array
    {
        if (count($sets) !== 3) {
            throw new BadRequestHttpException('The score must contain exactly 3 sets.');
        }

        return array_map(function (array $set, int $index): array {
            if (!array_key_exists('teamA', $set) || !array_key_exists('teamB', $set)) {
                throw new BadRequestHttpException(sprintf('Set %d must contain teamA and teamB.', $index + 1));
            }

            $teamA = (int) $set['teamA'];
            $teamB = (int) $set['teamB'];

            if ($teamA < 0 || $teamB < 0 || $teamA > 7 || $teamB > 7) {
                throw new BadRequestHttpException(sprintf('Set %d must contain games between 0 and 7.', $index + 1));
            }

            return ['teamA' => $teamA, 'teamB' => $teamB];
        }, $sets, array_keys($sets));
    }

    public function winner(array $sets): string
    {
        $teamAWon = 0;
        $teamBWon = 0;

        foreach ($sets as $set) {
            if ($set['teamA'] === $set['teamB']) {
                continue;
            }

            $set['teamA'] > $set['teamB'] ? ++$teamAWon : ++$teamBWon;
        }

        if ($teamAWon === $teamBWon) {
            throw new BadRequestHttpException('The score must have a winning team.');
        }

        return $teamAWon > $teamBWon ? 'A' : 'B';
    }

    public function margin(array $sets): int
    {
        return array_reduce($sets, fn (int $carry, array $set) => $carry + abs($set['teamA'] - $set['teamB']), 0);
    }
}
