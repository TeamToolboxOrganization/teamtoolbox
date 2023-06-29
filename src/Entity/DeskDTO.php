<?php

namespace App\Entity;

class DeskDTO {
    private int $id;
    private int $x = 0;
    private int $y = 0;
    private bool $available = true;
    private bool $me = false;
    private string $name;

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return DeskDTO
     */
    public function setId(int $id): DeskDTO {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getX(): int {
        return $this->x;
    }

    /**
     * @param int $x
     *
     * @return DeskDTO
     */
    public function setX(int $x): DeskDTO {
        $this->x = $x;

        return $this;
    }

    /**
     * @return int
     */
    public function getY(): int {
        return $this->y;
    }

    /**
     * @param int $y
     *
     * @return DeskDTO
     */
    public function setY(int $y): DeskDTO {
        $this->y = $y;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool {
        return $this->available;
    }

    /**
     * @param bool $available
     *
     * @return DeskDTO
     */
    public function setAvailable(bool $available): DeskDTO {
        $this->available = $available;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMe(): bool {
        return $this->me;
    }

    /**
     * @param bool $me
     *
     * @return DeskDTO
     */
    public function setMe(bool $me): DeskDTO {
        $this->me = $me;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return DeskDTO
     */
    public function setName(?string $name): DeskDTO {
        $this->name = $name;

        return $this;
    }



}
