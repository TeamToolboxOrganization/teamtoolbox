<?php
namespace App\Entity;

class MindsetDTO
{
    /**
     * MindsetDTO constructor.
     * @param $tendance
     * @param $value
     */
    public function __construct(?float $tendance, ?float $value)
    {
        $this->tendance = $tendance;
        $this->value = $value;
    }

    /**
     * @var float
     */
    private $tendance;

    /**
     * @var float
     */
    private $value;

    /**
     * @return float
     */
    public function getTendance(): float
    {
        return $this->tendance;
    }

    /**
     * @param float $tendance
     * @return MindsetDTO
     */
    public function setTendance(float $tendance): MindsetDTO
    {
        $this->tendance = $tendance;
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
     * @return MindsetDTO
     */
    public function setValue(float $value): MindsetDTO
    {
        $this->value = $value;
        return $this;
    }

}