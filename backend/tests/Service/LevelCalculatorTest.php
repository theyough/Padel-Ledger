<?php

namespace App\Tests\Service;

use App\Entity\PadelMatch;
use App\Entity\Player;
use App\Entity\ScoreProposal;
use App\Service\LevelCalculator;
use App\Service\ScoreValidator;
use PHPUnit\Framework\TestCase;

class LevelCalculatorTest extends TestCase
{
    public function testValidatedWinIncreasesWinnerRatingsAndDecreasesLoserRatings(): void
    {
        $teamAPlayer1 = $this->player(420);
        $teamAPlayer2 = $this->player(430);
        $teamBPlayer1 = $this->player(470);
        $teamBPlayer2 = $this->player(480);
        $match = new PadelMatch($teamAPlayer1, $teamAPlayer2, $teamBPlayer1, $teamBPlayer2, $teamAPlayer1);
        $proposal = new ScoreProposal($match, $teamAPlayer1, [
            ['teamA' => 6, 'teamB' => 4],
            ['teamA' => 6, 'teamB' => 4],
            ['teamA' => 0, 'teamB' => 0],
        ]);

        $changes = (new LevelCalculator(new ScoreValidator()))->applyValidatedMatch($match, $proposal);

        self::assertCount(4, $changes);
        self::assertGreaterThan(420, $teamAPlayer1->getRating());
        self::assertGreaterThan(430, $teamAPlayer2->getRating());
        self::assertLessThan(470, $teamBPlayer1->getRating());
        self::assertLessThan(480, $teamBPlayer2->getRating());
    }

    private function player(float $rating): Player
    {
        return (new Player())
            ->setEmail(sprintf('player-%d@example.test', (int) $rating))
            ->setFirstName('Test')
            ->setLastName('Player')
            ->setPasswordHash('hash')
            ->setRating($rating);
    }
}
