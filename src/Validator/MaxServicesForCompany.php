<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class MaxServicesForCompany extends Constraint
{
    public $message = 'A company cannot have more than {{ limit }} services.';
    public $max = 10; // Default to 10

    public function __construct(array $options = null)
    {
        parent::__construct($options);
    }

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}
