<?php

namespace App\Tests\Service;

use App\Service\ScoreValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ScoreValidatorTest extends TestCase
{
    public function testItNormalizesExactlyThreeSets(): void
    {
        $validator = new ScoreValidator();

        $sets = $validator->normalize([
            ['teamA' => '6', 'teamB' => '4'],
            ['teamA' => 3, 'teamB' => 6],
            ['teamA' => 7, 'teamB' => 5],
        ]);

        self::assertSame([
            ['teamA' => 6, 'teamB' => 4],
            ['teamA' => 3, 'teamB' => 6],
            ['teamA' => 7, 'teamB' => 5],
        ], $sets);
        self::assertSame('A', $validator->winner($sets));
    }

    public function testItRejectsScoresWithoutAWinner(): void
    {
        $validator = new ScoreValidator();

        $this->expectException(BadRequestHttpException::class);

        $validator->winner([
            ['teamA' => 6, 'teamB' => 4],
            ['teamA' => 4, 'teamB' => 6],
            ['teamA' => 0, 'teamB' => 0],
        ]);
    }
}
