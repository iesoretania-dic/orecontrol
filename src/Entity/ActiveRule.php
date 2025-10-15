<?php

namespace App\Entity;

use App\Repository\ActiveRuleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActiveRuleRepository::class)]
class ActiveRule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\ManyToOne(inversedBy: 'activeRules')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Network $network = null;

    #[ORM\ManyToOne(inversedBy: 'activeRules')]
    #[ORM\JoinColumn(nullable: false)]
    private ?RuleGroup $ruleGroup = null;

    #[ORM\ManyToOne]
    private ?Person $createdBy = null;

    #[ORM\Column(length: 255)]
    private ?string $ip = null;

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

    public function getNetwork(): ?Network
    {
        return $this->network;
    }

    public function setNetwork(?Network $network): static
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

    public function getCreatedBy(): ?Person
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Person $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): static
    {
        $this->ip = $ip;

        return $this;
    }
}
