<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ObjectiveThemeRepository")
 * @ORM\Table(name="objective_theme")
 */
class ObjectiveTheme
{
    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="objectiveThemes")
     */
    private $users;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $progress;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ObjectiveTheme
     */
    public function setId(int $id): ObjectiveTheme
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return ObjectiveTheme
     */
    public function setTitle(string $title): ObjectiveTheme
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return ObjectiveTheme
     */
    public function setDescription(string $description): ObjectiveTheme
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param ArrayCollection $users
     * @return ObjectiveTheme
     */
    public function setUsers(Collection $users): ObjectiveTheme
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @return int
     */
    public function getProgress(): ?int
    {
        return $this->progress;
    }

    /**
     * @param int $progress
     * @return ObjectiveTheme
     */
    public function setProgress(int $progress): ObjectiveTheme
    {
        $this->progress = $progress;
        return $this;
    }

    /**
     * @param User $user
     * @return void
     */
    public function removeUser(User $user)
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }
    }
}