<?php
namespace App\Entity;

class MindsetHistoryDTO
{
    /**
     * MindsetHistoryDTO constructor.
     * @param $publishedAt
     * @param $value
     */
    public function __construct(?\DateTime $publishedAt, ?float $value)
    {
        $this->publishedAt = $publishedAt;
        $this->value = $value;
    }

    /**
     * @var \DateTime
     */
    private $publishedAt;

    /**
     * @var float
     */
    private $value;

    /**
     * @return \DateTime
     */
    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    /**
     * @param \DateTime $publishedAt
     * @return MindsetHistoryDTO
     */
    public function setPublishedAt(?\DateTime $publishedAt): MindsetHistoryDTO
    {
        $this->publishedAt = $publishedAt;
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
     * @return MindsetHistoryDTO
     */
    public function setValue(?float $value): MindsetHistoryDTO
    {
        $this->value = $value;
        return $this;
    }

}