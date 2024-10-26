<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class HasMadeReservation extends Constraint
{
    public $message = "You didn't book this service";

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
