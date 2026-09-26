<?php

namespace App\Entity;

use App\Repository\RuleLogRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * One row per period during which a Network had a given RuleGroup (or "Acceso completo",
 * ruleGroup = null) applied: who/where started it, and who/where ended it (deletedAt
 * null while the period is still the network's current state).
 */
#[ORM\Entity(repositoryClass: RuleLogRepository::class)]
class RuleLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Network $network = null;

    #[ORM\ManyToOne]
    private ?RuleGroup $ruleGroup = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    private ?Person $createdBy = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $createdIp = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?ScheduledTask $createdScheduledTask = null;

    // Kept even after createdScheduledTask turns NULL (task deleted), so the history
    // can still say "tarea programada" instead of falling back to "IP autorizada".
    #[ORM\Column]
    private bool $createdViaScheduledTask = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\ManyToOne]
    private ?Person $deletedBy = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deletedIp = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?ScheduledTask $deletedScheduledTask = null;

    #[ORM\Column]
    private bool $deletedViaScheduledTask = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNetwork(): ?Network
    {
        return $this->network;
    }

    public function setNetwork(Network $network): static
    {
        $this->network = $network;

        return $this;
    }

    public function getRuleGroup(): ?RuleGroup
    {
        return $this->ruleGroup;
    }

    public function setRuleGroup(?RuleGroup $ruleGroup): static
    {
        $this->ruleGroup = $ruleGroup;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?Person
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Person $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedIp(): ?string
    {
        return $this->createdIp;
    }

    public function setCreatedIp(?string $createdIp): static
    {
        $this->createdIp = $createdIp;

        return $this;
    }

    public function getCreatedScheduledTask(): ?ScheduledTask
    {
        return $this->createdScheduledTask;
    }

    public function setCreatedScheduledTask(?ScheduledTask $createdScheduledTask): static
    {
        $this->createdScheduledTask = $createdScheduledTask;

        return $this;
    }

    public function isCreatedViaScheduledTask(): bool
    {
        return $this->createdViaScheduledTask;
    }

    public function setCreatedViaScheduledTask(bool $createdViaScheduledTask): static
    {
        $this->createdViaScheduledTask = $createdViaScheduledTask;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getDeletedBy(): ?Person
    {
        return $this->deletedBy;
    }

    public function setDeletedBy(?Person $deletedBy): static
    {
        $this->deletedBy = $deletedBy;

        return $this;
    }

    public function getDeletedIp(): ?string
    {
        return $this->deletedIp;
    }

    public function setDeletedIp(?string $deletedIp): static
    {
        $this->deletedIp = $deletedIp;

        return $this;
    }

    public function getDeletedScheduledTask(): ?ScheduledTask
    {
        return $this->deletedScheduledTask;
    }

    public function setDeletedScheduledTask(?ScheduledTask $deletedScheduledTask): static
    {
        $this->deletedScheduledTask = $deletedScheduledTask;

        return $this;
    }

    public function isDeletedViaScheduledTask(): bool
    {
        return $this->deletedViaScheduledTask;
    }

    public function setDeletedViaScheduledTask(bool $deletedViaScheduledTask): static
    {
        $this->deletedViaScheduledTask = $deletedViaScheduledTask;

        return $this;
    }
}
