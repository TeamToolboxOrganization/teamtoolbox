<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GanttLinkRepository")
 * @ORM\Table(name="gantt_link")
 */
class GanttLink
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
     * @var GanttTask
     *
     * @ORM\OneToOne(targetEntity="App\Entity\GanttTask")
     * @ORM\JoinColumn(name="source", nullable=false)
     */
    private $source;

    /**
     * @var GanttTask
     *
     * @ORM\OneToOne(targetEntity="App\Entity\GanttTask")
     * @ORM\JoinColumn(name="target", nullable=false)
     */
    private $target;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    private $type;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return GanttLink
     */
    public function setId(int $id): GanttLink
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return GanttTask
     */
    public function getSource(): GanttTask
    {
        return $this->source;
    }

    /**
     * @param GanttTask $source
     * @return GanttLink
     */
    public function setSource(GanttTask $source): GanttLink
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return GanttTask
     */
    public function getTarget(): GanttTask
    {
        return $this->target;
    }

    /**
     * @param GanttTask $target
     * @return GanttLink
     */
    public function setTarget(GanttTask $target): GanttLink
    {
        $this->target = $target;
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
     * @return GanttLink
     */
    public function setType(string $type): GanttLink
    {
        $this->type = $type;
        return $this;
    }

}