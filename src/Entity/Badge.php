<?php

namespace App\Entity;

use App\Repository\BadgeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BadgeRepository::class)]
class Badge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $name;

    #[ORM\Column(length: 100, unique: true)]
    private string $slug;

    #[ORM\Column(length: 10)]
    private string $emoji;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hint = null;

    /** XP minimum pour débloquer ce badge */
    #[ORM\Column(options: ['default' => 0])]
    
    private int $xpRequired = 0;

    /** Nombre d'activités complétées requises (0 = non utilisé) */
    #[ORM\Column(options: ['default' => 0])]
    private int $activitiesRequired = 0;

    // ── Getters / Setters ────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $n): static { $this->name = $n; return $this; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $s): static { $this->slug = $s; return $this; }

    public function getEmoji(): string { return $this->emoji; }
    public function setEmoji(string $e): static { $this->emoji = $e; return $this; }

    public function getHint(): ?string { return $this->hint; }
    public function setHint(?string $h): static { $this->hint = $h; return $this; }

    public function getXpRequired(): int { return $this->xpRequired; }
    public function setXpRequired(int $x): static { $this->xpRequired = $x; return $this; }

    public function getActivitiesRequired(): int { return $this->activitiesRequired; }
    public function setActivitiesRequired(int $n): static { $this->activitiesRequired = $n; return $this; }

    public function toArray(bool $unlocked = false): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'slug'     => $this->slug,
            'emoji'    => $this->emoji,
            'hint'     => $this->hint,
            'unlocked' => $unlocked,
        ];
    }
}
