<?php

namespace App\Entity;

use App\Repository\RuleLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RuleLogRepository::class)]
class RuleLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\ManyToOne]
    private ?Person $createdBy = null;

    #[ORM\ManyToOne]
    private ?Person $deletedBy = null;

    #[ORM\Column(length: 255)]
    private ?string $createdIp = null;

    #[ORM\Column(length: 255)]
    private ?string $deletedIp = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(\DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

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

    public function getDeletedBy(): ?Person
    {
        return $this->deletedBy;
    }

    public function setDeletedBy(?Person $deletedBy): static
    {
        $this->deletedBy = $deletedBy;

        return $this;
    }

    public function getCreatedIp(): ?string
    {
        return $this->createdIp;
    }

    public function setCreatedIp(string $createdIp): static
    {
        $this->createdIp = $createdIp;

        return $this;
    }

    public function getDeletedIp(): ?string
    {
        return $this->deletedIp;
    }

    public function setDeletedIp(string $deletedIp): static
    {
        $this->deletedIp = $deletedIp;

        return $this;
    }
}
