<?php

// src/Validator/Constraints/MaxServicesForCompanyValidator.php

namespace App\Validator;

use App\Repository\ServiceRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class MaxServicesForCompanyValidator extends ConstraintValidator
{
    public function __construct(private readonly ServiceRepository $serviceRepository)
    {
     }

    public function validate($service, Constraint $constraint): void
    {
        if (!$constraint instanceof MaxServicesForCompany) {
            throw new UnexpectedTypeException($constraint, MaxServicesForCompany::class);
        }

        if (null === $service) {
            return;
        }

        if (!property_exists($service, 'company')) {
            throw new UnexpectedValueException($service, 'object');
        }

        // Fetch the number of services for this company
        $serviceCount = $this->serviceRepository->count(['company' => $service->getCompany()]);

        // Use the max parameter from the constraint (default or provided)
        if ($serviceCount >= $constraint->max) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ limit }}', $constraint->max)
                ->atPath('company')
                ->addViolation();
        }
    }
}
