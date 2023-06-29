<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ConfigurationRepository")
 * @ORM\Table(name="configuration")
 */
class Configuration {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private string $id;

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private string $key;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private string $value;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Configuration
     */
    public function setId(string $id): Configuration
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return Configuration
     */
    public function setKey(string $key): Configuration
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return Configuration
     */
    public function setValue(string $value): Configuration
    {
        $this->value = $value;
        return $this;
    }

}
