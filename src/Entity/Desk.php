<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DeskRepository")
 * @ORM\Table(name="desk")
 */
class Desk {
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private string $name;

    /**
     * @var DeskDate[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\DeskDate", mappedBy="desk")
     */
    private $deskDates;

    /**
     * @ORM\Column(type="integer")
     */
    private int $x;

    /**
     * @ORM\Column(type="integer")
     */
    private int $y;

    public function __construct() {
        $this->deskDates = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Desk
     */
    public function setId(int $id): Desk {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getX(): ?int {
        return $this->x;
    }

    /**
     * @param int $x
     */
    public function setX(int $x): void {
        $this->x = $x;
    }

    /**
     * @return int
     */
    public function getY(): ?int {
        return $this->y;
    }

    /**
     * @param int $y
     */
    public function setY(int $y): void {
        $this->y = $y;
    }

    /**
     * @return DeskDate[]|Collection
     */
    public function getDeskDates() {
        return $this->deskDates;
    }

    /**
     * @return string
     */
    public function getName(): ?string {
        return $this->name ?? null;
    }

    /**
     * @param string|null $name
     *
     * @return Desk
     */
    public function setName(?string $name): Desk {
        $this->name = $name;

        return $this;
    }

}
