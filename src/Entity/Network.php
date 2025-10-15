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

    /**
     * @var Collection<int, ActiveRule>
     */
    #[ORM\OneToMany(targetEntity: ActiveRule::class, mappedBy: 'network')]
    private Collection $activeRules;

    public function __construct()
    {
        $this->activeRules = new ArrayCollection();
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

    /**
     * @return Collection<int, ActiveRule>
     */
    public function getActiveRules(): Collection
    {
        return $this->activeRules;
    }

    public function addActiveRule(ActiveRule $activeRule): static
    {
        if (!$this->activeRules->contains($activeRule)) {
            $this->activeRules->add($activeRule);
            $activeRule->setNetwork($this);
        }

        return $this;
    }

    public function removeActiveRule(ActiveRule $activeRule): static
    {
        if ($this->activeRules->removeElement($activeRule)) {
            // set the owning side to null (unless already changed)
            if ($activeRule->getNetwork() === $this) {
                $activeRule->setNetwork(null);
            }
        }

        return $this;
    }
}
