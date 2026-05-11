<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [new Get(uriTemplate: '/score-validations/{id}', security: "object.getScoreProposal().getPadelMatch().hasPlayer(user)")],
    normalizationContext: ['groups' => ['score_validation:read']],
    security: "is_granted('ROLE_USER')"
)]
#[ORM\Table(name: 'score_validation')]
#[ORM\UniqueConstraint(name: 'uniq_score_validation_proposal_player', columns: ['score_proposal_id', 'player_id'])]
class ScoreValidation
{
    public const DECISION_APPROVED = 'approved';
    public const DECISION_REJECTED = 'rejected';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['score_validation:read', 'match:read', 'score:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ScoreProposal::class, inversedBy: 'validations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['score_validation:read'])]
    private ScoreProposal $scoreProposal;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['score_validation:read', 'match:read', 'score:read'])]
    private Player $player;

    #[ORM\Column(length: 20)]
    #[Groups(['score_validation:read', 'match:read', 'score:read'])]
    private string $decision;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['score_validation:read', 'match:read', 'score:read'])]
    private ?string $comment = null;

    #[ORM\Column]
    #[Groups(['score_validation:read', 'match:read', 'score:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct(ScoreProposal $scoreProposal, Player $player, string $decision, ?string $comment = null)
    {
        $this->scoreProposal = $scoreProposal;
        $this->player = $player;
        $this->decision = $decision;
        $this->comment = $comment;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScoreProposal(): ScoreProposal
    {
        return $this->scoreProposal;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getDecision(): string
    {
        return $this->decision;
    }

    public function setDecision(string $decision): self
    {
        $this->decision = $decision;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
