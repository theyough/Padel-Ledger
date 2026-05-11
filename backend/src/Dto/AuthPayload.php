<?php

namespace App\Dto;

use App\Entity\Player;
use Symfony\Component\Serializer\Attribute\Groups;

class AuthPayload
{
    public function __construct(
        #[Groups(['auth:read'])]
        public string $token,
        #[Groups(['auth:read'])]
        public Player $player
    ) {
    }
}
