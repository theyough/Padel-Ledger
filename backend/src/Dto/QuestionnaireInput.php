<?php

namespace App\Dto;

class QuestionnaireInput
{
    public float $experienceYears = 0.0;

    public int $matchesPerMonth = 0;

    public string $competitionLevel = 'none';

    public int $consistency = 1;

    public int $glassUsage = 1;

    public int $tacticalAwareness = 1;

    public int $technicalShots = 1;

    public function toArray(): array
    {
        return [
            'experienceYears' => $this->experienceYears,
            'matchesPerMonth' => $this->matchesPerMonth,
            'competitionLevel' => $this->competitionLevel,
            'consistency' => $this->consistency,
            'glassUsage' => $this->glassUsage,
            'tacticalAwareness' => $this->tacticalAwareness,
            'technicalShots' => $this->technicalShots,
        ];
    }
}
