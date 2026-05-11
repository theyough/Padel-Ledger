<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Dto\CreateMatchInput;
use App\Dto\RejectScoreInput;
use App\State\ApproveCurrentScoreProcessor;
use App\State\CreateMatchProcessor;
use App\State\FinishMatchProcessor;
use App\State\MatchCollectionProvider;
use App\State\RejectCurrentScoreProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(uriTemplate: '/matches/{id}', security: 'object.hasPlayer(user)'),
        new GetCollection(uriTemplate: '/matches', provider: MatchCollectionProvider::class),
        new Post(uriTemplate: '/matches', input: CreateMatchInput::class, processor: CreateMatchProcessor::class),
        new Post(
            uriTemplate: '/matches/{id}/finish',
            input: false,
            read: false,
            deserialize: false,
            processor: FinishMatchProcessor::class,
            name: 'finish_match'
        ),
        new Post(
            uriTemplate: '/matches/{id}/score-proposals/current/approve',
            input: false,
            read: false,
            deserialize: false,
            processor: ApproveCurrentScoreProcessor::class,
            name: 'approve_current_score'
        ),
        new Post(
            uriTemplate: '/matches/{id}/score-proposals/current/reject',
            input: RejectScoreInput::class,
            read: false,
            processor: RejectCurrentScoreProcessor::class,
            name: 'reject_current_score'
        ),
    ],
    normalizationContext: ['groups' => ['match:read']],
    security: "is_granted('ROLE_USER')"
)]
#[ORM\Table(name: 'padel_match')]
class PadelMatch
{
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_PENDING_SCORE = 'pending_score';
    public const STATUS_PENDING_VALIDATION = 'pending_validation';
    public const STATUS_VALIDATED = 'validated';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['match:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'team_a_player1_id', referencedColumnName: 'id', nullable: false)]
    private Player $teamAPlayer1;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'team_a_player2_id', referencedColumnName: 'id', nullable: false)]
    private Player $teamAPlayer2;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'team_b_player1_id', referencedColumnName: 'id', nullable: false)]
    private Player $teamBPlayer1;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'team_b_player2_id', referencedColumnName: 'id', nullable: false)]
    private Player $teamBPlayer2;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Player $createdBy;

    #[ORM\ManyToOne(targetEntity: ScoreProposal::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['match:read'])]
    private ?ScoreProposal $currentScoreProposal = null;

    #[ORM\OneToMany(mappedBy: 'padelMatch', targetEntity: ScoreProposal::class)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    #[Groups(['match:read'])]
    private Collection $scoreProposals;

    #[ORM\Column(length: 40)]
    #[Groups(['match:read'])]
    private string $status = self::STATUS_SCHEDULED;

    #[ORM\Column(nullable: true)]
    #[Groups(['match:read'])]
    private ?\DateTimeImmutable $scheduledAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['match:read'])]
    private ?\DateTimeImmutable $finishedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['match:read'])]
    private ?\DateTimeImmutable $validatedAt = null;

    #[ORM\Column]
    #[Groups(['match:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        Player $teamAPlayer1,
        Player $teamAPlayer2,
        Player $teamBPlayer1,
        Player $teamBPlayer2,
        Player $createdBy,
        ?\DateTimeImmutable $scheduledAt = null,
    ) {
        $this->teamAPlayer1 = $teamAPlayer1;
        $this->teamAPlayer2 = $teamAPlayer2;
        $this->teamBPlayer1 = $teamBPlayer1;
        $this->teamBPlayer2 = $teamBPlayer2;
        $this->createdBy = $createdBy;
        $this->scheduledAt = $scheduledAt;
        $this->createdAt = new \DateTimeImmutable();
        $this->scoreProposals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups(['match:read'])]
    #[SerializedName('teamA')]
    public function getTeamAPlayers(): array
    {
        return [$this->teamAPlayer1, $this->teamAPlayer2];
    }

    #[Groups(['match:read'])]
    #[SerializedName('teamB')]
    public function getTeamBPlayers(): array
    {
        return [$this->teamBPlayer1, $this->teamBPlayer2];
    }

    public function getPlayers(): array
    {
        return [$this->teamAPlayer1, $this->teamAPlayer2, $this->teamBPlayer1, $this->teamBPlayer2];
    }

    public function getCreatedBy(): Player
    {
        return $this->createdBy;
    }

    public function getCurrentScoreProposal(): ?ScoreProposal
    {
        return $this->currentScoreProposal;
    }

    public function getScoreProposals(): Collection
    {
        return $this->scoreProposals;
    }

    public function addScoreProposal(ScoreProposal $scoreProposal): self
    {
        if (!$this->scoreProposals->contains($scoreProposal)) {
            $this->scoreProposals->add($scoreProposal);
        }

        return $this;
    }

    public function setCurrentScoreProposal(?ScoreProposal $currentScoreProposal): self
    {
        $this->currentScoreProposal = $currentScoreProposal;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getScheduledAt(): ?\DateTimeImmutable
    {
        return $this->scheduledAt;
    }

    public function markFinished(): self
    {
        $this->status = self::STATUS_PENDING_SCORE;
        $this->finishedAt = new \DateTimeImmutable();

        return $this;
    }

    public function markPendingValidation(): self
    {
        $this->status = self::STATUS_PENDING_VALIDATION;

        return $this;
    }

    public function markValidated(): self
    {
        $this->status = self::STATUS_VALIDATED;
        $this->validatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function getValidatedAt(): ?\DateTimeImmutable
    {
        return $this->validatedAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function hasPlayer(Player $player): bool
    {
        foreach ($this->getPlayers() as $matchPlayer) {
            if ($matchPlayer->getId() === $player->getId()) {
                return true;
            }
        }

        return false;
    }
}
