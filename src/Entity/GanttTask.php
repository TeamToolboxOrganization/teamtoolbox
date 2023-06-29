<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GanttTaskRepository")
 * @ORM\Table(name="gantt_task")
 */
class GanttTask
{

    const TYPE_PROJECT = 'project';
    const TYPE_MILESTONE = 'milestone';
    const TYPE_TASK = 'task';
    const TYPES = [self::TYPE_PROJECT];

    const JIRA_TYPE_EPIC = 'epic';
    const JIRA_TYPE_US = 'us';
    const JIRA_TYPE_TASK = 'task';
    const JIRA_TYPE_SPIKE = 'spike';
    const JIRA_TYPE_BUG = 'bug';
    const JIRA_TYPE_DEFECT = 'defect';
    const JIRA_TYPES = [self::JIRA_TYPE_DEFECT,self::JIRA_TYPE_BUG,self::JIRA_TYPE_EPIC,self::JIRA_TYPE_US,self::JIRA_TYPE_TASK,self::JIRA_TYPE_SPIKE ];

    public function __construct()
    {
        $this->startDate = new \DateTime();
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
     * @ORM\Column(name="key", type="string", nullable=false)
     */
    private $key;

    /**
     * @var string
     * @ORM\Column(name="text", type="string", nullable=false)
     */
    private $text;

    /**
     * @var string
     * @Assert\Choice(choices=GanttTask::TYPES, message="Choose a valid Type")
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    private $type;

    /**
     * @var string
     * @Assert\Choice(choices=GanttTask::JIRA_TYPES, message="Choose a valid Type")
     * @ORM\Column(name="jira_type", type="string", nullable=false)
     */
    private $jiraType;

    /**
     * @var string
     * @ORM\Column(name="squad", type="string", nullable=false)
     */
    private $squad;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime", nullable=false)
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=false)
     */
    private $endDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="duration", type="integer", nullable=false)
     */
    private $duration;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deadline", type="datetime", nullable=false)
     */
    private $deadline;

    /**
     * @var float
     *
     * @ORM\Column(name="progress", type="float", nullable=false)
     */
    private $progress;

    /**
     * @var integer
     *
     * @ORM\Column(name="sortorder", type="integer", nullable=true)
     */
    private $sortOrder;

    /**
     * @var GanttTask
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\GanttTask", fetch="EAGER")
     * @ORM\JoinColumn(name="parent", nullable=true)
     */
    private $parent;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(nullable=true)
     */
    private $owner;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return GanttTask
     */
    public function setId(int $id): GanttTask
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return GanttTask
     */
    public function setText(string $text): GanttTask
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     * @return GanttTask
     */
    public function setStartDate(\DateTime $startDate): GanttTask
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     * @return GanttTask
     */
    public function setDuration(int $duration): GanttTask
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDeadline(): ?\DateTime
    {
        return $this->deadline;
    }

    /**
     * @param \DateTime $deadline
     * @return GanttTask
     */
    public function setDeadline(?\DateTime $deadline): GanttTask
    {
        $this->deadline = $deadline;
        return $this;
    }

    /**
     * @return float
     */
    public function getProgress(): float
    {
        return $this->progress;
    }

    /**
     * @param float $progress
     * @return GanttTask
     */
    public function setProgress(float $progress): GanttTask
    {
        $this->progress = $progress;
        return $this;
    }

    /**
     * @return GanttTask
     */
    public function getParent(): ?GanttTask
    {
        return $this->parent;
    }

    /**
     * @param GanttTask $parent
     * @return GanttTask
     */
    public function setParent(?GanttTask $parent): GanttTask
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return User
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     * @return GanttTask
     */
    public function setOwner(?User $owner): GanttTask
    {
        $this->owner = $owner;
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
     * @return GanttTask
     */
    public function setType(?string $type): GanttTask
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return GanttTask
     */
    public function setKey(?string $key): GanttTask
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     * @return GanttTask
     */
    public function setSortOrder(?int $sortOrder): GanttTask
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    /**
     * @return string
     */
    public function getJiraType(): ?string
    {
        return $this->jiraType;
    }

    /**
     * @param string $jiraType
     * @return GanttTask
     */
    public function setJiraType(?string $jiraType): GanttTask
    {
        $this->jiraType = $jiraType;
        return $this;
    }

    /**
     * @return string
     */
    public function getSquad(): ?string
    {
        return $this->squad;
    }

    /**
     * @param string $squad
     * @return GanttTask
     */
    public function setSquad(?string $squad): GanttTask
    {
        $this->squad = $squad;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     * @return GanttTask
     */
    public function setEndDate(\DateTime $endDate): GanttTask
    {
        $this->endDate = $endDate;
        return $this;
    }

}