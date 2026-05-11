<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Dto\QuestionnaireInput;
use App\State\LevelEstimateProcessor;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/questionnaire/level',
            input: QuestionnaireInput::class,
            output: LevelEstimate::class,
            processor: LevelEstimateProcessor::class,
            name: 'estimate_level'
        ),
    ],
)]
class LevelEstimate
{
    public function __construct(
        public int $level,
        public float $rating
    ) {
    }
}
