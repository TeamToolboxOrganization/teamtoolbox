<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MepRepository")
 * @ORM\Table(name="mep")
 */
class Mep extends UserDate
{

    const STATE_TOCONFIRM = 'à confirmer';
    const STATE_CONFIRM = 'Validé';
    const STATE_CANCELED = 'Annulé';

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private string $state;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private string $version;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private string $comment;


    /**
     * @return string
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string $state
     * @return Mep
     */
    public function setState(string $state): Mep
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * @param string $version
     * @return Mep
     */
    public function setVersion(string $version): Mep
    {
        $this->version = $version;
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
     * @return Mep
     */
    public function setComment(?string $comment): Mep
    {
        $this->comment = $comment;
        return $this;
    }

}