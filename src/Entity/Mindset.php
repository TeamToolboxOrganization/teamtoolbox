<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="mindset")
 */
class Mindset
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @var float
     * @Assert\Range(
     *      min = 0,
     *      max = 10,
     *      notInRangeMessage = "Choisir une valeur entre {{ min }} et {{ max }}",
     * )
     * @ORM\Column(type="float", nullable=true)
     */
    private $value;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(nullable=true)
     */
    private $collab;


    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(nullable=true)
     */
    private $author;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Mindset
     */
    public function setId(int $id): Mindset
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return Mindset
     */
    public function setDate(?\DateTime $date): Mindset
    {
        $this->date = $date;
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
     * @return Mindset
     */
    public function setValue(float $value): Mindset
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return User
     */
    public function getCollab(): User
    {
        return $this->collab;
    }

    /**
     * @param User $collab
     * @return Mindset
     */
    public function setCollab(User $collab): Mindset
    {
        $this->collab = $collab;
        return $this;
    }

    /**
     * @return User
     */
    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * @param User $author
     * @return Mindset
     */
    public function setAuthor(User $author): Mindset
    {
        $this->author = $author;
        return $this;
    }

}
