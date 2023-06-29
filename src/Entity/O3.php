<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class O3
 * Represents a date for an O3 event
 * @ORM\Entity(repositoryClass="App\Repository\O3Repository")
 * @ORM\Table(name="o3")
 */
class O3 extends UserDate
{
    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", fetch="LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $collaborator;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="outlook_event_id", nullable=true)
     */
    protected ?string $outlookEventId;

    // Getters and setters
    public function getCollaborator(): ?User
    {
        return $this->collaborator;
    }

    public function setCollaborator(?User $collaborator): self
    {
        $this->collaborator = $collaborator;

        return $this;
    }

    public function getOutlookEventId(): ?string
    {
        return $this->outlookEventId;
    }

    public function setOutlookEventId(?string $outlookEventId): self
    {
        $this->outlookEventId = $outlookEventId;

        return $this;
    }

    // Other properties and methods
}