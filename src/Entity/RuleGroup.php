<?php

namespace App\Entity;

use App\Repository\RuleGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RuleGroupRepository::class)]
class RuleGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $site_id = null;

    #[ORM\Column(length: 255)]
    private ?string $_id = null;

    #[ORM\Column(length: 255)]
    private ?string $group_type = null;

    #[ORM\Column]
    private ?bool $selectable = null;

    /**
     * @var Collection<int, Network>
     */
    #[ORM\OneToMany(targetEntity: Network::class, mappedBy: 'ruleGroup')]
    private Collection $networks;

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

    public function getSiteId(): ?string
    {
        return $this->site_id;
    }

    public function setSiteId(string $site_id): static
    {
        $this->site_id = $site_id;

        return $this;
    }

    public function setId(string $_id): static
    {
        $this->_id = $_id;

        return $this;
    }

    public function getGroupType(): ?string
    {
        return $this->group_type;
    }

    public function setGroupType(string $group_type): static
    {
        $this->group_type = $group_type;

        return $this;
    }

    public function isSelectable(): ?bool
    {
        return $this->selectable;
    }

    public function setSelectable(bool $selectable): static
    {
        $this->selectable = $selectable;

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
            $network->setRuleGroup($this);
        }

        return $this;
    }

    public function removeNetwork(Network $network): static
    {
        if ($this->networks->removeElement($network)) {
            // set the owning side to null (unless already changed)
            if ($network->getRuleGroup() === $this) {
                $network->setRuleGroup(null);
            }
        }

        return $this;
    }
}
