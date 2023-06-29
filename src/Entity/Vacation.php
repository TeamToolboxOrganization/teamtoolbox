<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VacationRepository")
 * @ORM\Table(name="vacation")
 */
class Vacation
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $collab;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $value;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $startAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $endAt;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $state;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Vacation
     */
    public function setId(int $id): Vacation
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return User
     */
    public function getCollab(): ?User
    {
        return $this->collab;
    }

    /**
     * @param User $collab
     * @return Vacation
     */
    public function setCollab(User $collab): Vacation
    {
        $this->collab = $collab;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Vacation
     */
    public function setType(string $type): Vacation
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return float
     */
    public function getValue(): ?float
    {
        return $this->value;
    }

    /**
     * @param float $value
     * @return Vacation
     */
    public function setValue(float $value): Vacation
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartAt(): ?\DateTime
    {
        return $this->startAt;
    }

    /**
     * @param \DateTime $startAt
     * @return Vacation
     */
    public function setStartAt(\DateTime $startAt): Vacation
    {
        $this->startAt = $startAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndAt(): ?\DateTime
    {
        return $this->endAt;
    }

    /**
     * @param \DateTime $endAt
     * @return Vacation
     */
    public function setEndAt(\DateTime $endAt): Vacation
    {
        $this->endAt = $endAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string $state
     * @return Vacation
     */
    public function setState(?string $state): Vacation
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastDay(): ?\DateTime
    {
        if($this->getEndAt() == null){
            return null;
        }

        if($this->getEndAt()->format('Hi') == "2359"){
            return $this->getEndAt()->add(new \DateInterval('P1D'));
        };

        return $this->getEndAt();
    }
}