<?php

namespace App\Entity;

use App\Repository\PollOptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PollOptionRepository::class)]
class PollOption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pollOptions')]
    private ?poll $poll = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $textOption = null;

    #[ORM\Column(nullable: true)]
    private ?int $totalVote = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPoll(): ?poll
    {
        return $this->poll;
    }

    public function setPoll(?poll $poll): static
    {
        $this->poll = $poll;

        return $this;
    }

    public function getTextOption(): ?string
    {
        return $this->textOption;
    }

    public function setTextOption(string $textOption): static
    {
        $this->textOption = $textOption;

        return $this;
    }

    public function getTotalVote(): ?int
    {
        return $this->totalVote;
    }

    public function setTotalVote(?int $totalVote): static
    {
        $this->totalVote = $totalVote;

        return $this;
    }
}
