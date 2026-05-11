<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\LevelEstimate;
use App\Dto\QuestionnaireInput;
use App\Service\QuestionnaireLevelEstimator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class LevelEstimateProcessor implements ProcessorInterface
{
    public function __construct(private readonly QuestionnaireLevelEstimator $levelEstimator)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): LevelEstimate
    {
        if (!$data instanceof QuestionnaireInput) {
            throw new BadRequestHttpException('Invalid questionnaire payload.');
        }

        $level = $this->levelEstimator->estimate($data->toArray());

        return new LevelEstimate($level, $level * 100.0);
    }
}
