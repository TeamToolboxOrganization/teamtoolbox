<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DeskDateRepository")
 * @ORM\Table(name="desk_date")
 */
class DeskDate extends UserDate
{

    /**
     * @var Desk
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Desk", inversedBy="deskDates")
     * @ORM\JoinColumn(nullable=true)
     */
    private $desk;

    /**
     * @return Desk
     */
    public function getDesk(): ?Desk
    {
        return $this->desk;
    }

    /**
     * @param Desk $desk
     * @return DeskDate
     */
    public function setDesk(?Desk $desk): DeskDate
    {
        $this->desk = $desk;
        return $this;
    }

}