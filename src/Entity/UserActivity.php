<?php

namespace App\Entity;

use App\Repository\UserActivityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserActivityRepository::class)]
#[ORM\UniqueConstraint(name: 'user_activity_unique', columns: ['user_id', 'activity_id'])]
class UserActivity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userActivities')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Activity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Activity $activity;

    /** 'not_started' | 'ongoing' | 'completed' */
    #[ORM\Column(length: 20, options: ['default' => 'not_started'])]
    private string $status = 'not_started';

    #[ORM\Column(options: ['default' => 0])]
    private int $progress = 0;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    // ── Getters / Setters ────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getUser(): User { return $this->user; }
    public function setUser(User $u): static { $this->user = $u; return $this; }

    public function getActivity(): Activity { return $this->activity; }
    public function setActivity(Activity $a): static { $this->activity = $a; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $s): static { $this->status = $s; return $this; }

    public function getProgress(): int { return $this->progress; }
    public function setProgress(int $p): static { $this->progress = max(0, min(100, $p)); return $this; }

    public function getStartedAt(): ?\DateTimeImmutable { return $this->startedAt; }
    public function setStartedAt(?\DateTimeImmutable $d): static { $this->startedAt = $d; return $this; }

    public function getCompletedAt(): ?\DateTimeImmutable { return $this->completedAt; }
    public function setCompletedAt(?\DateTimeImmutable $d): static { $this->completedAt = $d; return $this; }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'status'      => $this->status,
            'progress'    => $this->progress,
            'startedAt'   => $this->startedAt?->format(\DateTimeInterface::ATOM),
            'completedAt' => $this->completedAt?->format(\DateTimeInterface::ATOM),
        ];
    }
}
