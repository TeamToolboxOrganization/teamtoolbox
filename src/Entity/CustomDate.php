<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CustomDateRepository")
 * @ORM\Table(name="custom_date")
 */
class CustomDate extends UserDate
{

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $comment;

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return CustomDate
     */
    public function setName(string $name): CustomDate
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     * @return CustomDate
     */
    public function setComment(string $comment): CustomDate
    {
        $this->comment = $comment;
        return $this;
    }

}