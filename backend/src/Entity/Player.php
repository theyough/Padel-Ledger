<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Dto\AuthPayload;
use App\Dto\RegisterPlayerInput;
use App\State\CurrentPlayerProvider;
use App\State\RegisterPlayerProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(uriTemplate: '/players/{id}', security: "is_granted('ROLE_USER')"),
        new GetCollection(uriTemplate: '/players', security: "is_granted('ROLE_USER')"),
        new Get(
            uriTemplate: '/me',
            uriVariables: [],
            read: false,
            provider: CurrentPlayerProvider::class,
            security: "is_granted('ROLE_USER')",
            name: 'current_player'
        ),
        new Post(
            uriTemplate: '/auth/register',
            input: RegisterPlayerInput::class,
            output: AuthPayload::class,
            processor: RegisterPlayerProcessor::class,
            normalizationContext: ['groups' => ['auth:read', 'player:read']],
            name: 'register_player'
        ),
    ],
    normalizationContext: ['groups' => ['player:read']],
)]
#[ORM\Table(name: 'player')]
#[ORM\UniqueConstraint(name: 'uniq_player_email', columns: ['email'])]
class Player implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['player:read', 'match:read', 'score:read', 'score_validation:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['player:read'])]
    private string $email;

    #[ORM\Column(length: 255)]
    private string $passwordHash;

    #[ORM\Column(length: 80)]
    #[Groups(['player:read', 'match:read', 'score:read', 'score_validation:read'])]
    private string $firstName;

    #[ORM\Column(length: 80)]
    #[Groups(['player:read', 'match:read', 'score:read', 'score_validation:read'])]
    private string $lastName;

    #[ORM\Column]
    #[Groups(['player:read', 'match:read', 'score:read', 'score_validation:read'])]
    private int $level = 1;

    #[ORM\Column]
    #[Groups(['player:read'])]
    private float $rating = 100.0;

    #[ORM\Column]
    #[Groups(['player:read'])]
    private int $matchCount = 0;

    #[ORM\Column(type: 'json')]
    private array $questionnaireAnswers = [];

    #[ORM\Column]
    #[Groups(['player:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    #[Groups(['player:read'])]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = strtolower(trim($email));

        return $this;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getPassword(): ?string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): self
    {
        $this->passwordHash = $passwordHash;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = trim($firstName);

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = trim($lastName);

        return $this;
    }

    #[Groups(['player:read', 'match:read', 'score:read', 'score_validation:read'])]
    public function getFullName(): string
    {
        return trim($this->firstName.' '.$this->lastName);
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = max(1, min(8, $level));
        $this->rating = max($this->rating, $this->level * 100.0);
        $this->touch();

        return $this;
    }

    public function getRating(): float
    {
        return $this->rating;
    }

    public function setRating(float $rating): self
    {
        $this->rating = max(100.0, min(899.0, $rating));
        $this->level = (int) max(1, min(8, floor(($this->rating - 100.0) / 100.0) + 1));
        $this->touch();

        return $this;
    }

    public function getMatchCount(): int
    {
        return $this->matchCount;
    }

    public function incrementMatchCount(): self
    {
        ++$this->matchCount;
        $this->touch();

        return $this;
    }

    public function getQuestionnaireAnswers(): array
    {
        return $this->questionnaireAnswers;
    }

    public function setQuestionnaireAnswers(array $questionnaireAnswers): self
    {
        $this->questionnaireAnswers = $questionnaireAnswers;
        $this->touch();

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): self
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
