<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @Target({"CLASS", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class SameDayLeaveComeBackAfternoon extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public $message = 'vacation.error.sameDayLeaveComeBackAfternoon';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
