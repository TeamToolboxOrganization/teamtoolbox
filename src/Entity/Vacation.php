<?php
namespace App\Entity;
use App\Form\Type\DateTimePickerType;
use App\Repository\VacationRepository;
use App\Validator\AlreadyInVacation;
use App\Validator\EnoughVacation;
use App\Validator\SameDayLeaveComeBackAfternoon;
use Doctrine\Bundle\DoctrineBundle\Dbal\ManagerRegistryAwareConnectionProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VacationRepository")
 * @ORM\Table(name="vacation")
 * @EnoughVacation()
 * @SameDayLeaveComeBackAfternoon()
 * @AlreadyInVacation()
 */
class Vacation
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $collab;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $value;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    #[Assert\GreaterThanOrEqual(
        value: "today",
        message: 'vacation.error.beforeToday')]
    private $startAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    #[Assert\GreaterThanOrEqual(
        propertyPath: "startAt",
        message: 'vacation.error.startEndDateError')]
    private $endAt;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $state;

    /**
     * @var bool
     */
    private $startAtPm;

    /**
     * @var bool
     */
    private $endAtAm;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Vacation
     */
    public function setId(int $id): Vacation
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return User
     */
    public function getCollab(): ?User
    {
        return $this->collab;
    }

    /**
     * @param User $collab
     * @return Vacation
     */
    public function setCollab(User $collab): Vacation
    {
        $this->collab = $collab;
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
     * @return Vacation
     */
    public function setType(string $type): Vacation
    {
        $this->type = $type;
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
     * @return Vacation
     */
    public function setValue(): Vacation
    {
        // Si le jour est un samedi ou un dimanche, on le soustrait du calcul
        $startDate = new \DateTime($this->getStartAt()->format('Y-m-d H:i:s'));
        $value = round(($this->getEndAt()->getTimestamp() - $this->getStartAt()->getTimestamp()) / 60 / 60 / 24, 1);
        if($value != 0.5){
            $weekEndDaysCount = 0;
            if($value % 2 === 0){
                for($i = 0; $i <= $value; $i++){
                    date_modify($startDate, "+1day");
                    if($startDate->format('D') === "Sat" || $startDate->format('D') === "Sun"){
                        $weekEndDaysCount++;
                    }
                }
            }
            else{
                for($i = 0; $i < $value; $i++){
                    date_modify($startDate, "+1day");
                    if($startDate->format('D') === "Sat" || $startDate->format('D') === "Sun"){
                        $weekEndDaysCount++;
                    }
                }
            }
            $this->value = $value - $weekEndDaysCount;
        }
        else{
            $this->value = $value;
        }
        return $this;
    }

/**
 * @return \DateTime
 */
public function getStartAt(): ?\DateTime
{
    return $this->startAt;
}

/**
 * @param \DateTime $startAt
 * @return Vacation
 */
public function setStartAt(\DateTime $startAt): Vacation
{
    if($this->getStartAtPm()){
        $this->startAt = date_modify($startAt, '+11hours+59minutes');
    }
    else{
        $this->startAt = $startAt;
    }
    return $this;
}

/**
 * @return \DateTime
 */
public function getEndAt(): ?\DateTime
{
    return $this->endAt;
}

/**
 * @param \DateTime $endAt
 * @return Vacation
 */
public function setEndAt(\DateTime $endAt): Vacation
{
    if($this->getEndAtAm()){
        $this->endAt = date_modify($endAt, '+11hours59minutes');
    }
    else{
        $this->endAt = date_modify($endAt, '+23hours+59minutes');
    }

    return $this;
}

/**
 * @return string
 */
public function getState(): ?string
{
    return $this->state;
}

/**
 * @param string $state
 * @return Vacation
 */
public function setState(?string $state): Vacation
{
    $this->state = $state;
    return $this;
}

/**
 * @return \DateTime
 */
public function getLastDay(): ?\DateTime
{
    if($this->getEndAt() == null){
        return null;
    }

    if($this->getEndAt()->format('Hi') == "2359"){
        return $this->getEndAt()->add(new \DateInterval('P1D'));
    };

    return $this->getEndAt();
}

/**
 * @return bool
 */
public function getStartAtPm(): bool
{
    return $this->startAtPm;
}

/**
 * @param bool $startAtPm
 * @return $this
 */
public function setStartAtPm(?bool $startAtPm): Vacation
{
    $this->startAtPm = $startAtPm;
    return $this;
}

/**
 * @return bool
 */
public function getEndAtAm(): bool
{
    return $this->endAtAm;
}

/**
 * @param bool $endAtAm
 * @return $this
 */
public function setEndAtAm(?bool $endAtAm): Vacation
{
    $this->endAtAm = $endAtAm;
    return $this;
}
}