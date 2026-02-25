<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private string $name;

    #[ORM\Column(length: 10)]
    private string $emoji;

    /** 'hebdo' | 'special' | 'quotidien' */
    #[ORM\Column(length: 20)]
    private string $category;

    #[ORM\Column]
    private int $xpReward;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    // ── Getters / Setters ────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $n): static { $this->name = $n; return $this; }

    public function getEmoji(): string { return $this->emoji; }
    public function setEmoji(string $e): static { $this->emoji = $e; return $this; }

    public function getCategory(): string { return $this->category; }
    public function setCategory(string $c): static { $this->category = $c; return $this; }

    public function getXpReward(): int { return $this->xpReward; }
    public function setXpReward(int $x): static { $this->xpReward = $x; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $d): static { $this->description = $d; return $this; }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'emoji'       => $this->emoji,
            'cat'         => $this->category,
            'xp'          => $this->xpReward,
            'description' => $this->description,
        ];
    }
}
