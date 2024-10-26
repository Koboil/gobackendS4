<?php

namespace App\Validator;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Review;
use App\Entity\Reservation;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class HasMadeReservationValidator extends ConstraintValidator
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly Security $security)
    {

    }

    public function validate($complaint, Constraint $constraint): void
    {
        if (!$complaint instanceof Review) {
            throw new UnexpectedValueException($complaint, Review::class);
        }

        if (!$constraint instanceof HasMadeReservation) {
            throw new UnexpectedValueException($constraint, HasMadeReservation::class);
        }

        if ($this->security->isGranted(["ROLE_ADMIN"])) {
            return;
        }
        $user = $complaint->getUser() ?? null;
        $reservation = $this->entityManager->getRepository(Reservation::class)->findOneBy(["customer" => $user, "service" => $complaint->getService()]);

        if (!$reservation) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
