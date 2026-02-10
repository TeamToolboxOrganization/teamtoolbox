<?php

namespace App\Validator;

use App\Repository\VacationRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Translation\TranslatableMessage;

class EnoughVacationValidator extends ConstraintValidator
{
    /**
     * @var VacationRepository
     */
    private $vacationRepository;

    /**
     * @param VacationRepository $vacationRepository
     */
    public function __construct(VacationRepository $vacationRepository)
    {
        $this->vacationRepository = $vacationRepository;
    }

    /**
     * @param $vacation
     * @param Constraint $constraint
     * @return void
     * @throws \Exception
     */
    public function validate($vacation, Constraint $constraint)
    {
        /* @var App\Validator\EnoughVacation $constraint */

        if($vacation->getType() === "Congés payés" || $vacation->getType() === "RTT"){
            $vacation->setValue();
            if($vacation->getType() === "Congés payés" ){
                $year = date_modify(new \DateTime('today'), '-1 year');
                $sumVacation = $this->vacationRepository->countCPForYear($vacation->getCollab()->getId(), $year->format('Y'));
            }
            else{
                $year = new \DateTime('today');
                $sumVacation = $this->vacationRepository->countRTTForYear($vacation->getCollab()->getId(), $year->format('Y'));
            }
            $sumVacation = $sumVacation[0]['sumVac'] == null ? 0 : $sumVacation[0]['sumVac'];
            $totalVacation = $vacation->getType() === "Congés payés" ? 25 : 10;

            if($totalVacation - $sumVacation - $vacation->getValue() <= 0)
            {
                $this->context->buildViolation(new TranslatableMessage($constraint->message, [], 'validators'))
                    ->atPath('type')
                    ->addViolation();
            }
        }
    }
}
