<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ScheduledTaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScheduledTaskRepository::class)]
class ScheduledTask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private bool $enabled = true;

    #[ORM\Column]
    private bool $allNetworks = false;

    /**
     * @var Collection<int, Network>
     */
    #[ORM\ManyToMany(targetEntity: Network::class)]
    #[ORM\JoinTable(name: 'scheduled_task_network')]
    private Collection $networks;

    #[ORM\ManyToOne]
    private ?RuleGroup $targetRuleGroup = null;

    /**
     * @var int[] ISO-8601 weekdays: 1 (Monday) .. 7 (Sunday)
     */
    #[ORM\Column]
    private array $weekdays = [];

    #[ORM\Column(type: 'time_immutable')]
    private ?\DateTimeImmutable $time = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastRunAt = null;

    #[ORM\ManyToOne]
    private ?Person $createdBy = null;

    public function __construct()
    {
        $this->networks = new ArrayCollection();
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

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isAllNetworks(): bool
    {
        return $this->allNetworks;
    }

    public function setAllNetworks(bool $allNetworks): static
    {
        $this->allNetworks = $allNetworks;

        return $this;
    }

    /**
     * @return Collection<int, Network>
     */
    public function getNetworks(): Collection
    {
        return $this->networks;
    }

    public function addNetwork(Network $network): static
    {
        if (!$this->networks->contains($network)) {
            $this->networks->add($network);
        }

        return $this;
    }

    public function removeNetwork(Network $network): static
    {
        $this->networks->removeElement($network);

        return $this;
    }

    public function clearNetworks(): static
    {
        $this->networks->clear();

        return $this;
    }

    public function getTargetRuleGroup(): ?RuleGroup
    {
        return $this->targetRuleGroup;
    }

    public function setTargetRuleGroup(?RuleGroup $targetRuleGroup): static
    {
        $this->targetRuleGroup = $targetRuleGroup;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getWeekdays(): array
    {
        return $this->weekdays;
    }

    /**
     * @param int[] $weekdays
     */
    public function setWeekdays(array $weekdays): static
    {
        $this->weekdays = array_values(array_unique(array_map('intval', $weekdays)));

        return $this;
    }

    public function getTime(): ?\DateTimeImmutable
    {
        return $this->time;
    }

    public function setTime(\DateTimeImmutable $time): static
    {
        $this->time = $time;

        return $this;
    }

    public function getLastRunAt(): ?\DateTimeImmutable
    {
        return $this->lastRunAt;
    }

    public function setLastRunAt(?\DateTimeImmutable $lastRunAt): static
    {
        $this->lastRunAt = $lastRunAt;

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
}
