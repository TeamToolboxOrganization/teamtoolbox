<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SquadRepository")
 * @ORM\Table(name="squad")
 */
class Squad
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
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $picture;

    /**
     * @var Collection
     * @ORM\OneToMany(
     *     targetEntity="User",
     *     mappedBy="squad",
     *     fetch="EAGER"
     * )
     */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Squad
     */
    public function setId(int $id): Squad
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Squad
     */
    public function setName(string $name): Squad
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPicture(): ?string
    {
        return $this->picture;
    }

    /**
     * @param string $picture
     * @return Squad
     */
    public function setPicture(string $picture): Squad
    {
        $this->picture = $picture;
        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): ?Collection
    {
        return $this->users;
    }

    /**
     * @param ArrayCollection $users
     * @return Squad
     */
    public function setUsers(ArrayCollection $users): Squad
    {
        $this->users = $users;
        return $this;
    }

    public function addUser(User $user)
    {
        if ($this->users->contains($user)) {
            return;
        }
        $this->users[] = $user;
        // not needed for persistence, just keeping both sides in sync
        $user->setSquad($this);
    }

    public function removeUser(User $user)
    {
        if ($this->users->contains($user)) {
            return;
        }
        $this->users->removeElement($user);
        // not needed for persistence, just keeping both sides in sync
        $user->setSquad(null);
    }

    public function __toString()
    {
        return $this->getName();
    }
}