<?php

namespace App\Dto;

class CreateMatchInput
{
    /**
     * @var int[]
     */
    public array $teamA = [];

    /**
     * @var int[]
     */
    public array $teamB = [];

    public ?string $scheduledAt = null;
}
