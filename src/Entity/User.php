<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Ignore;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    const ROLE_ADMIN = 'Administrateur';
    const ROLE_MANAGER = 'Manager';
    const ROLE_LT = 'Lead de Squad';
    const ROLE_MEP_ORGA = 'Plannificateur de MEP';
    const ROLE_USER = 'Utilisateur';
    const ROLE_SCREEN = 'Afficheur';
    const ROLES = [self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_LT, self::ROLE_MEP_ORGA, self::ROLE_USER, self::ROLE_SCREEN];

    public function __construct() {
        $this->objectiveThemes = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->collabs = new ArrayCollection();
    }

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $idts;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $employeeid;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $idjira;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $apikeyjira;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $apikeyazdo;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $fullName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=50)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Ignore()
     */
    private $password;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="collabs")
     * @ORM\JoinColumn(nullable=true)
     */
    private $manager;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="manager")
     * @ORM\JoinColumn(nullable=true, name="manager_id")
     */
    private $collabs;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $birthday;

    /**
     * @var Squad
     * @ORM\ManyToOne(targetEntity="Squad", inversedBy="users")
     * @ORM\JoinColumn(name="squad", referencedColumnName="id", nullable="true")
     */
    private $squad;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $picture;

    /**
     * @ORM\ManyToMany(targetEntity="ObjectiveTheme", inversedBy="users")
     * @ORM\JoinTable(name="users_objectivethemes")
     */
    private $objectiveThemes;

    /**
     * @ORM\ManyToMany(targetEntity="Project", inversedBy="users")
     * @ORM\JoinTable(name="users_projects")
     */
    private $projects;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $sharedata;

    /**
     * @var MsToken
     *
     * @ORM\OneToOne(targetEntity="App\Entity\MsToken", fetch="EAGER")
     * @ORM\JoinColumn(name="mstokenid", nullable=true)
     */
    private $msToken;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $analytics;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $wizard;

    /**
     * @var Desk
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Desk")
     * @ORM\JoinColumn(nullable=true)
     */
    private $defaultDesk;

    /**
     * @var Category
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Category")
     * @ORM\JoinColumn(nullable=true)
     */
    private $defaultActivity;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $defaultProduct;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setFullName(string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return User
     */
    public function getManager(): ?User
    {
        return $this->manager;
    }

    /**
     * @param User $manager
     * @return User
     */
    public function setManager(?User $manager): User
    {
        $this->manager = $manager;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBirthday(): ?\DateTime
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime $birthday
     * @return User
     */
    public function setBirthday(?\DateTime $birthday): User
    {
        $this->birthday = $birthday;
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
     * @return User
     */
    public function setPicture(string $picture): User
    {
        $this->picture = $picture;
        return $this;
    }

    /**
     * @return int
     */
    public function getIdts(): ?int
    {
        return $this->idts;
    }

    /**
     * @param int $idts
     * @return User
     */
    public function setIdts(int $idts): User
    {
        $this->idts = $idts;
        return $this;
    }

    /**
     * @return int
     */
    public function getEmployeeid(): ?int
    {
        return $this->employeeid;
    }

    /**
     * @param int $employeeid
     * @return User
     */
    public function setEmployeeid(int $employeeid): User
    {
        $this->employeeid = $employeeid;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdjira(): ?string
    {
        return $this->idjira;
    }

    /**
     * @param string $idjira
     * @return User
     */
    public function setIdjira(string $idjira): User
    {
        $this->idjira = $idjira;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getApikeyjira(): ?string
    {
        return $this->apikeyjira;
    }

    /**
     * @param string|null $apikeyjira
     * @return User
     */
    public function setApikeyjira(?string $apikeyjira): User
    {
        $this->apikeyjira = $apikeyjira;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getApikeyazdo(): ?string
    {
        return $this->apikeyazdo;
    }

    /**
     * @param string|null $apikeyazdo
     * @return User
     */
    public function setApikeyazdo(?string $apikeyazdo): User
    {
        $this->apikeyazdo = $apikeyazdo;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSharedata(): bool
    {
        return $this->sharedata;
    }

    /**
     * @param bool $sharedata
     * @return User
     */
    public function setSharedata(bool $sharedata): User
    {
        $this->sharedata = $sharedata;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getObjectiveThemes(): \Doctrine\Common\Collections\Collection
    {
        return $this->objectiveThemes;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection  $objectiveThemes
     * @return User
     */
    public function setObjectiveThemes(\Doctrine\Common\Collections\Collection $objectiveThemes): User
    {
        $this->objectiveThemes = $objectiveThemes;
        return $this;
    }

    /**
     * @return MsToken|null
     */
    public function getMsToken(): ?MsToken
    {
        return $this->msToken;
    }

    /**
     * @param MsToken $msToken
     * @return User
     */
    public function setMsToken(?MsToken $msToken): User
    {
        $this->msToken = $msToken;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAnalytics(): bool
    {
        return $this->analytics;
    }

    /**
     * @param bool $analytics
     * @return User
     */
    public function setAnalytics(bool $analytics): User
    {
        $this->analytics = $analytics;
        return $this;
    }

    /**
     * Returns the roles or permissions granted to the user for security.
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // guarantees that a user always has at least one role for security
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        // We're using bcrypt in security.yaml to encode the password, so
        // the salt value is built-in and and you don't have to generate one
        // See https://en.wikipedia.org/wiki/Bcrypt

        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        // if you had a plainPassword property, you'd nullify it here
        // $this->plainPassword = null;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        return serialize([$this->id, $this->username, $this->password]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        [$this->id, $this->username, $this->password] = unserialize($serialized, ['allowed_classes' => false]);
    }

    public function getUserIdentifier(): string
    {
        return $this->getId();
    }

    /**
     * @return Squad
     */
    public function getSquad(): ?Squad
    {
        return $this->squad;
    }

    /**
     * @param Squad $squad
     * @return User
     */
    public function setSquad(?Squad $squad): User
    {
        $this->squad = $squad;
        return $this;
    }

    /**
     * @param ObjectiveTheme $objectiveTheme
     * @return void
     */
    public function removeObjectiveTheme(ObjectiveTheme $objectiveTheme)
    {
        if ($this->objectiveThemes->contains($objectiveTheme)) {
            $this->objectiveThemes->removeElement($objectiveTheme);
        }
    }

    /**
     * @return bool
     */
    public function isWizard(): bool
    {
        return $this->wizard;
    }

    /**
     * @param bool $wizard
     * @return User
     */
    public function setWizard(bool $wizard): User
    {
        $this->wizard = $wizard;
        return $this;
    }

    /**
     * @return Desk
     */
    public function getDefaultDesk(): ?Desk
    {
        return $this->defaultDesk;
    }

    /**
     * @param ?Desk $defaultDesk
     * @return User
     */
    public function setDefaultDesk(?Desk $defaultDesk): User
    {
        $this->defaultDesk = $defaultDesk;
        return $this;
    }

    /**
     * @return ?Collection
     */
    public function getProjects(): ?Collection
    {
        return $this->projects;
    }

    /**
     * @param ?Collection $projects
     * @return User
     */
    public function setProjects(?Collection $projects): User
    {
        $this->projects = $projects;
        return $this;
    }

    /**
     * @return ?Collection
     */
    public function getCollabs()
    {
        return $this->collabs;
    }

    /**
     * @param Collection $collabs
     * @return User
     */
    public function setCollabs($collabs)
    {
        $this->collabs = $collabs;
        return $this;
    }

    /**
     * @return Category|null
     */
    public function getDefaultActivity(): ?Category
    {
        return $this->defaultActivity;
    }

    /**
     * @param Category|null $defaultActivity
     * @return User
     */
    public function setDefaultActivity(?Category $defaultActivity): User
    {
        $this->defaultActivity = $defaultActivity;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultProduct(): ?string
    {
        return $this->defaultProduct;
    }

    /**
     * @param string $defaultProduct
     * @return User
     */
    public function setDefaultProduct(?string $defaultProduct): User
    {
        $this->defaultProduct = $defaultProduct;
        return $this;
    }

    public function __serialize(): array
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        return [$this->id, $this->username, $this->password];
    }

    public function __unserialize(array $data): void
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        [$this->id, $this->username, $this->password] = $data;
    }
}
