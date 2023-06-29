<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="user_date")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\Entity(repositoryClass="App\Repository\UserDateRepository")
 */
abstract class UserDate
{
    const TYPE_MEP = 'mep';
    const TYPE_OFFICE = 'office';
    const TYPE_03 = 'o3';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $collab;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $startAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $endAt;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", name="am_pm", nullable=true)
     */
    protected $amPm;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return UserDate
     */
    public function setId(int $id): UserDate
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
     * @return UserDate
     */
    public function setCollab(?User $collab): UserDate
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
     * @return UserDate
     */
    public function setType(string $type): UserDate
    {
        $this->type = $type;
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
     * @return UserDate
     */
    public function setStartAt(\DateTime $startAt): UserDate
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
     * @return UserDate
     */
    public function setEndAt(\DateTime $endAt): UserDate
    {
        $this->endAt = $endAt;
        return $this;
    }

    /**
     * @return int
     */
    public function getAmPm(): ?int
    {
        return $this->amPm;
    }

    /**
     * @param int $amPm
     * @return UserDate
     */
    public function setAmPm(int $amPm): UserDate
    {
        $this->amPm = $amPm;
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