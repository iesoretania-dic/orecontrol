<?php

namespace App\Entity;

use App\Repository\NetworkRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NetworkRepository::class)]
class Network
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $ruleDescription = null;

    #[ORM\Column(length: 255)]
    private ?string $allowed_ip = null;

    #[ORM\Column]
    private ?int $level = null;

    #[ORM\ManyToOne(inversedBy: 'networks')]
    private ?RuleGroup $ruleGroup = null;

    #[ORM\ManyToOne]
    private ?Person $enabledBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $enabledAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $enabledIp = null;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getRuleDescription(): ?string
    {
        return $this->ruleDescription;
    }

    public function setRuleDescription(string $ruleDescription): static
    {
        $this->ruleDescription = $ruleDescription;

        return $this;
    }

    public function getAllowedIp(): ?string
    {
        return $this->allowed_ip;
    }

    public function setAllowedIp(string $allowed_ip): static
    {
        $this->allowed_ip = $allowed_ip;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

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

    public function getEnabledBy(): ?Person
    {
        return $this->enabledBy;
    }

    public function setEnabledBy(?Person $enabledBy): static
    {
        $this->enabledBy = $enabledBy;

        return $this;
    }

    public function getEnabledAt(): ?\DateTimeImmutable
    {
        return $this->enabledAt;
    }

    public function setEnabledAt(?\DateTimeImmutable $enabledAt): static
    {
        $this->enabledAt = $enabledAt;

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

    public function getEnabledIp(): ?string
    {
        return $this->enabledIp;
    }

    public function setEnabledIp(?string $enabledIp): static
    {
        $this->enabledIp = $enabledIp;

        return $this;
    }
}
