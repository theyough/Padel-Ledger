<?php

namespace App\Service;

class QuestionnaireLevelEstimator
{
    public function estimate(array $answers): int
    {
        $score = 0;
        $score += $this->bucket((float) ($answers['experienceYears'] ?? 0), [0.2, 1, 3, 5, 8], 1.2);
        $score += $this->bucket((float) ($answers['matchesPerMonth'] ?? 0), [1, 4, 8, 12, 20], 1.0);
        $score += $this->scale((int) ($answers['consistency'] ?? 1), 1.2);
        $score += $this->scale((int) ($answers['glassUsage'] ?? 1), 1.1);
        $score += $this->scale((int) ($answers['tacticalAwareness'] ?? 1), 1.2);
        $score += $this->scale((int) ($answers['technicalShots'] ?? 1), 1.3);
        $score += $this->competitionBonus((string) ($answers['competitionLevel'] ?? 'none'));

        return (int) max(1, min(8, round($score / 7.0)));
    }

    private function bucket(float $value, array $thresholds, float $weight): float
    {
        $points = 1;
        foreach ($thresholds as $threshold) {
            if ($value >= $threshold) {
                ++$points;
            }
        }

        return min(8, $points) * $weight;
    }

    private function scale(int $value, float $weight): float
    {
        return max(1, min(8, $value)) * $weight;
    }

    private function competitionBonus(string $competitionLevel): float
    {
        return match ($competitionLevel) {
            'p25' => 3.0,
            'p50' => 4.0,
            'p100' => 5.0,
            'p250' => 6.0,
            'p500' => 7.0,
            'p1000_plus' => 8.0,
            default => 1.0,
        };
    }
}
