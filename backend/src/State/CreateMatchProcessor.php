<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CreateMatchInput;
use App\Entity\PadelMatch;
use App\Entity\Player;
use App\Service\MatchWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CreateMatchProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MatchWorkflow $matchWorkflow
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PadelMatch
    {
        if (!$data instanceof CreateMatchInput) {
            throw new BadRequestHttpException('Invalid match payload.');
        }

        $player = $this->matchWorkflow->currentPlayer();
        $teamAIds = array_map('intval', $data->teamA);
        $teamBIds = array_map('intval', $data->teamB);
        $allIds = array_merge($teamAIds, $teamBIds);

        if (count($teamAIds) !== 2 || count($teamBIds) !== 2 || count(array_unique($allIds)) !== 4) {
            throw new BadRequestHttpException('A match must contain 2 players per team and 4 distinct players.');
        }

        if (!in_array($player->getId(), $allIds, true)) {
            throw new AccessDeniedHttpException('You must be part of the match to create it.');
        }

        $players = $this->loadPlayersById($allIds);
        $scheduledAt = $data->scheduledAt ? new \DateTimeImmutable($data->scheduledAt) : null;
        $match = new PadelMatch(
            $players[$teamAIds[0]],
            $players[$teamAIds[1]],
            $players[$teamBIds[0]],
            $players[$teamBIds[1]],
            $player,
            $scheduledAt
        );

        $this->entityManager->persist($match);
        $this->entityManager->flush();

        return $match;
    }

    /**
     * @return array<int, Player>
     */
    private function loadPlayersById(array $ids): array
    {
        $players = [];
        foreach ($ids as $id) {
            $player = $this->entityManager->getRepository(Player::class)->find($id);
            if (!$player instanceof Player) {
                throw new BadRequestHttpException(sprintf('Player #%d was not found.', $id));
            }
            $players[$id] = $player;
        }

        return $players;
    }
}
