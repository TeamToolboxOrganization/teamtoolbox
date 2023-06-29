<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NoteRepository")
 * @ORM\Table(name="note")
 */
class Note
{
    const TYPE_ONETOONE = 'One-To-One';
    const TYPE_TOFOLLOW = 'Sujet à suivre';
    const TYPE_TODISCUSS = 'Sujet à aborder';
    const TYPE_TODO = 'Todo';
    const TYPE_NOTE = 'Note';
    const TYPES = [self::TYPE_ONETOONE, self::TYPE_TOFOLLOW, self::TYPE_TODO, self::TYPE_TODISCUSS, self::TYPE_NOTE];

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
     * @Assert\Choice(choices=Note::TYPES, message="Choose a valid Type")
     * @ORM\Column(type="string", nullable=true)
     */
    private $type;

    /**
     * @var string
     */
    private $content;

    /**
     * @return Mindset
     */
    public function getMindset(): ?Mindset
    {
        return $this->mindset;
    }

    /**
     * @param ?Mindset $mindset
     * @return Note
     */
    public function setMindset(?Mindset $mindset): Note
    {
        $this->mindset = $mindset;
        return $this;
    }

    /**
     * @var Mindset
     *
     * @ORM\ManyToOne(targetEntity="Mindset")
     * @ORM\JoinColumn(name="mindset", referencedColumnName="id", nullable="true")
     */
    private $mindset;

    /**
     * @var float
     */
    private $mindsetValue;

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
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $publishedAt;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $checked;

    public function __construct()
    {
        $this->publishedAt = new \DateTime();
        $this->checked = 0;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     * @return Note
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Note
     */
    public function setId(int $id): Note
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getCollab(): ?User
    {
        return $this->collab;
    }

    /**
     * @param User $collab
     * @return Note
     */
    public function setCollab(User $collab): Note
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
     * @return Note
     */
    public function setAuthor(User $author): Note
    {
        $this->author = $author;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    /**
     * @param \DateTime $publishedAt
     * @return Note
     */
    public function setPublishedAt(\DateTime $publishedAt): Note
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Note
     */
    public function setType(string $type): Note
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getChecked(): int
    {
        return $this->checked;
    }

    /**
     * @param int $checked
     * @return Note
     */
    public function setChecked(int $checked): Note
    {
        $this->checked = $checked;
        return $this;
    }

    /**
     * @return float
     */
    public function getMindsetValue(): ?float
    {
        return $this->mindsetValue;
    }

    /**
     * @param float $mindsetValue
     * @return Note
     */
    public function setMindsetValue(?float $mindsetValue): Note
    {
        $this->mindsetValue = $mindsetValue;
        return $this;
    }

    
}