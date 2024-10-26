<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ReservationAvailable extends Constraint
{
    public $message = 'Appointment is not available for the selected period.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
