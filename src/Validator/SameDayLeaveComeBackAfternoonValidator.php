<?php

namespace App\Validator;

use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SameDayLeaveComeBackAfternoonValidator extends ConstraintValidator
{
    public function validate($vacation, Constraint $constraint)
    {
        // Check si l'utilisateur veut partir et revenir l'aprem dans la même journée
        if($vacation->getStartAt() == $vacation->getEndAt())
        {
            if($vacation->getStartAtPm() && $vacation->getEndAtAm()){
                $this->context->buildViolation(new TranslatableMessage($constraint->message))
                    ->atPath('startAtPm')
                    ->addViolation();
            }
        }
    }
}
