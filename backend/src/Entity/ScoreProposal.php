<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Dto\ScoreProposalInput;
use App\State\CreateScoreProposalProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(uriTemplate: '/score-proposals/{id}', security: "object.getPadelMatch().hasPlayer(user)"),
        new Post(
            uriTemplate: '/matches/{id}/score-proposals',
            uriVariables: ['id' => new Link(fromClass: PadelMatch::class)],
            input: ScoreProposalInput::class,
            output: PadelMatch::class,
            read: false,
            processor: CreateScoreProposalProcessor::class,
            security: "is_granted('ROLE_USER')",
            name: 'create_score_proposal'
        ),
    ],
    normalizationContext: ['groups' => ['score:read']],
    security: "is_granted('ROLE_USER')"
)]
#[ORM\Table(name: 'score_proposal')]
class ScoreProposal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['score:read', 'match:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PadelMatch::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private PadelMatch $padelMatch;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['score:read', 'match:read'])]
    private Player $proposedBy;

    #[ORM\Column(type: 'json')]
    #[Groups(['score:read', 'match:read'])]
    private array $sets;

    #[ORM\Column(name: 'is_current')]
    #[Groups(['score:read', 'match:read'])]
    private bool $current = true;

    #[ORM\Column]
    #[Groups(['score:read', 'match:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: 'scoreProposal', targetEntity: ScoreValidation::class)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    #[Groups(['score:read', 'match:read'])]
    private Collection $validations;

    public function __construct(PadelMatch $padelMatch, Player $proposedBy, array $sets)
    {
        $this->padelMatch = $padelMatch;
        $this->proposedBy = $proposedBy;
        $this->sets = $sets;
        $this->createdAt = new \DateTimeImmutable();
        $this->validations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPadelMatch(): PadelMatch
    {
        return $this->padelMatch;
    }

    public function getProposedBy(): Player
    {
        return $this->proposedBy;
    }

    public function getSets(): array
    {
        return $this->sets;
    }

    public function isCurrent(): bool
    {
        return $this->current;
    }

    public function setCurrent(bool $current): self
    {
        $this->current = $current;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getValidations(): Collection
    {
        return $this->validations;
    }

    public function addValidation(ScoreValidation $validation): self
    {
        if (!$this->validations->contains($validation)) {
            $this->validations->add($validation);
        }

        return $this;
    }

    #[Groups(['score:read', 'match:read'])]
    public function getApprovedCount(): int
    {
        return $this->validations->filter(
            fn (ScoreValidation $validation) => $validation->getDecision() === ScoreValidation::DECISION_APPROVED
        )->count();
    }

    #[Groups(['score:read', 'match:read'])]
    public function getRejectedCount(): int
    {
        return $this->validations->filter(
            fn (ScoreValidation $validation) => $validation->getDecision() === ScoreValidation::DECISION_REJECTED
        )->count();
    }

    #[Groups(['score:read', 'match:read'])]
    public function getRequiredCount(): int
    {
        return count($this->padelMatch->getPlayers());
    }
}
