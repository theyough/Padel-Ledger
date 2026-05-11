<?php

namespace App\Tests\Service;

use App\Service\QuestionnaireLevelEstimator;
use PHPUnit\Framework\TestCase;

class QuestionnaireLevelEstimatorTest extends TestCase
{
    public function testBeginnerAnswersStayInTheLowerLevels(): void
    {
        $estimator = new QuestionnaireLevelEstimator();

        $level = $estimator->estimate([
            'experienceYears' => 0,
            'matchesPerMonth' => 0,
            'competitionLevel' => 'none',
            'consistency' => 1,
            'glassUsage' => 1,
            'tacticalAwareness' => 1,
            'technicalShots' => 1,
        ]);

        self::assertSame(1, $level);
    }

    public function testExperiencedTournamentPlayerReachesUpperLevels(): void
    {
        $estimator = new QuestionnaireLevelEstimator();

        $level = $estimator->estimate([
            'experienceYears' => 8,
            'matchesPerMonth' => 20,
            'competitionLevel' => 'p1000_plus',
            'consistency' => 8,
            'glassUsage' => 8,
            'tacticalAwareness' => 8,
            'technicalShots' => 8,
        ]);

        self::assertGreaterThanOrEqual(7, $level);
    }
}
