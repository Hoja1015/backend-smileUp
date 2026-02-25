<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private string $password;


    #[ORM\Column(length: 100)]
    private string $firstName;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $room = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $building = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $residence = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $residenceCode = null;

    /**
     * XP démarre OBLIGATOIREMENT à 0 pour tout nouvel utilisateur.
     * Ne jamais mettre de valeur positive par défaut.
     */
    #[ORM\Column(options: ['default' => 0])]
    private int $xp = 0;

    #[ORM\Column(options: ['default' => 500])]
    private int $xpMax = 500;

    #[ORM\Column(length: 20, options: ['default' => 'Bronze'])]
    private string $level = 'Bronze';

    /**
     * Badges débloqués (tableau d'emojis).
     * Démarre OBLIGATOIREMENT vide.
     */
    // #[ORM\Column(type: 'json', options: ['default' => '[]'])]
    #[ORM\Column(type: 'json')]
    private array $badges = [];

    #[ORM\Column(length: 7, options: ['default' => '#6c63ff'])]
    private string $color = '#6c63ff';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(targetEntity: UserActivity::class, mappedBy: 'user', cascade: ['remove'])]
    private Collection $userActivities;

    public function __construct()
    {
        $this->createdAt      = new \DateTimeImmutable();
        $this->userActivities = new ArrayCollection();
    }

    // ── Getters / Setters ────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getUserIdentifier(): string { return $this->email; }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }
    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }

    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    public function eraseCredentials(): void {}

    public function getFirstName(): string { return $this->firstName; }
    public function setFirstName(string $n): static { $this->firstName = $n; return $this; }

    public function getLastName(): ?string { return $this->lastName; }
    public function setLastName(?string $n): static { $this->lastName = $n; return $this; }

    public function getRoom(): ?string { return $this->room; }
    public function setRoom(?string $r): static { $this->room = $r; return $this; }

    public function getBuilding(): ?string { return $this->building; }
    public function setBuilding(?string $b): static { $this->building = $b; return $this; }

    public function getResidence(): ?string { return $this->residence; }
    public function setResidence(?string $r): static { $this->residence = $r; return $this; }

    public function getResidenceCode(): ?string { return $this->residenceCode; }
    public function setResidenceCode(?string $c): static { $this->residenceCode = $c; return $this; }

    public function getXp(): int { return $this->xp; }

    public function setXp(int $xp): static
    {
        $this->xp = max(0, $xp);
        $this->recalculateLevel();
        return $this;
    }

    public function addXp(int $amount): static
    {
        return $this->setXp($this->xp + $amount);
    }

    public function getXpMax(): int { return $this->xpMax; }
    public function getLevel(): string { return $this->level; }

    public function getBadges(): array { return $this->badges; }

    public function addBadge(string $badgeEmoji): static
    {
        if (!in_array($badgeEmoji, $this->badges, true)) {
            $this->badges[] = $badgeEmoji;
        }
        return $this;
    }

    public function getColor(): string { return $this->color; }
    public function setColor(string $c): static { $this->color = $c; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    // ── Méthodes calculées ───────────────────────────────────────

    public function getName(): string
    {
        return trim($this->firstName . ' ' . ($this->lastName ?? ''));
    }

    public function getInitials(): string
    {
        $parts = array_filter([$this->firstName, $this->lastName]);
        return strtoupper(
            implode('', array_map(fn(string $p) => mb_substr($p, 0, 1), $parts))
        );
    }

    /**
     * Recalcule automatiquement level et xpMax en fonction de l'XP.
     * Bronze : 0–499 | Silver : 500–1999 | Gold : 2000–4999 | Legend : 5000+
     */
    private function recalculateLevel(): void
    {
        $this->level = match (true) {
            $this->xp >= 5000 => 'Legend',
            $this->xp >= 2000 => 'Gold',
            $this->xp >= 500  => 'Silver',
            default           => 'Bronze',
        };

        $this->xpMax = match ($this->level) {
            'Bronze' => 500,
            'Silver' => 2000,
            'Gold'   => 5000,
            'Legend' => 9999,
        };
    }

    // ── Sérialisation pour l'API ─────────────────────────────────

    public function toArray(int $rank = 0): array
    {
        return [
            'id'          => $this->id,
            'email'       => $this->email,
            'firstName'   => $this->firstName,
            'lastName'    => $this->lastName,
            'name'        => $this->getName(),
            'initials'    => $this->getInitials(),
            'color'       => $this->color,
            'room'        => $this->room,
            'building'    => $this->building,
            'residence'   => $this->residence,
            'xp'          => $this->xp,
            'xpMax'       => $this->xpMax,
            'level'       => $this->level,
            'badges'      => $this->badges,
            'rank'        => $rank,
            'createdAt'   => $this->createdAt->format('Y-m-d'),
        ];
    }
}
