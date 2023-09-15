<?php

namespace App\Entity;

use App\Repository\RegistrationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RegistrationRepository::class)]
class Registration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'registrations')]
    private ?event $event = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $registrationDate = null;

    #[ORM\ManyToOne(inversedBy: 'registrations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?user $user = null;

    #[ORM\Column]
    private ?bool $isInvited = false;

    #[ORM\Column]
    private ?bool $hasConfirmed = false;

    #[ORM\Column]
    private ?bool $hasParticipated = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?event
    {
        return $this->event;
    }

    public function setEvent(?event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getRegistrationDate(): ?\DateTimeInterface
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(\DateTimeInterface $registrationDate): static
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function isIsInvited(): ?bool
    {
        return $this->isInvited;
    }

    public function setIsInvited(bool $isInvited): self
    {
        $this->isInvited = $isInvited;

        return $this;
    }

    public function isHasConfirmed(): ?bool
    {
        return $this->hasConfirmed;
    }

    public function setHasConfirmed(bool $hasConfirmed): self
    {
        $this->hasConfirmed = $hasConfirmed;

        return $this;
    }

    public function isHasParticipated(): ?bool
    {
        return $this->hasParticipated;
    }

    public function setHasParticipated(bool $hasParticipated): static
    {
        $this->hasParticipated = $hasParticipated;

        return $this;
    }
}
