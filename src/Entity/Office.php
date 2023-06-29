<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Office
 * Represents a date where a user is present at the office
 * @ORM\Entity(repositoryClass="App\Repository\OfficeRepository")
 * @ORM\Table(name="office")
 */
class Office extends UserDate
{

    /**
     * @var int
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private int $importFromRhpi;

    /**
     * @return int
     */
    public function getImportFromRhpi(): int
    {
        return $this->importFromRhpi;
    }

    /**
     * @param int $importFromRhpi
     * @return Office
     */
    public function setImportFromRhpi(int $importFromRhpi): Office
    {
        $this->importFromRhpi = $importFromRhpi;
        return $this;
    }



}