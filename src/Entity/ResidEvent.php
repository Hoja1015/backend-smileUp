<?php

namespace App\Entity;

use App\Repository\ResidEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResidEventRepository::class)]
class ResidEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private string $name;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $emoji = null;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $date;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $time = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $place = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $xpReward = 0;

    // ── Getters / Setters ────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $n): static { $this->name = $n; return $this; }

    public function getEmoji(): ?string { return $this->emoji; }
    public function setEmoji(?string $e): static { $this->emoji = $e; return $this; }

    public function getDate(): \DateTimeInterface { return $this->date; }
    public function setDate(\DateTimeInterface $d): static { $this->date = $d; return $this; }

    public function getTime(): ?string { return $this->time; }
    public function setTime(?string $t): static { $this->time = $t; return $this; }

    public function getPlace(): ?string { return $this->place; }
    public function setPlace(?string $p): static { $this->place = $p; return $this; }

    public function getXpReward(): int { return $this->xpReward; }
    public function setXpReward(int $x): static { $this->xpReward = $x; return $this; }

    public function toArray(): array
    {
        return [
            'id'    => $this->id,
            'name'  => ($this->emoji ? $this->emoji . ' ' : '') . $this->name,
            'day'   => $this->date->format('d'),
            'month' => (new \IntlDateFormatter('fr_FR', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'MMM'))->format($this->date),
            'meta'  => implode(' • ', array_filter([$this->time, $this->place])),
            'xp'    => $this->xpReward,
        ];
    }
}
