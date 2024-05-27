<?php

namespace App\Validator;

use App\Repository\VacationRepository;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AlreadyInVacationValidator extends ConstraintValidator
{
    private $vacationRepository;

    public function __construct(VacationRepository $vacationRepository)
    {
        $this->vacationRepository = $vacationRepository;
    }
    public function validate($vacation, Constraint $constraint)
    {
        $upcomingVacationStart = $this->vacationRepository->getCurrentVacation($vacation->getStartAt(), $this->vacationRepository->stateNotOk);
        $upcomingVacationEnd = $this->vacationRepository->getCurrentVacation($vacation->getEndAt(), $this->vacationRepository->stateNotOk);
        if(!empty($upcomingVacationStart) || !empty($upcomingVacationEnd)){
            $this->context->buildViolation(new TranslatableMessage($constraint->message))
                ->atPath('startAt')
                ->addViolation();
        }
    }
}
