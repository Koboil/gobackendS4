<?php


namespace App\Validator;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Reservation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ReservationAvailableValidator extends ConstraintValidator
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
     }

    public function validate($reservation, Constraint $constraint): void
    {
        if (!$reservation instanceof Reservation) {
            throw new UnexpectedValueException($reservation, Reservation::class);
        }

        if (!$constraint instanceof ReservationAvailable) {
            throw new UnexpectedValueException($constraint, ReservationAvailable::class);
        }

        $reservationFrom = $reservation->getStartAt() ?? null;
        $reservationTo = $reservation->getEndAt() ?? null;

        $provider = $reservation->getProvider() ?? null;
        if ($provider === null) {
            return;
        }
        $existingReservations = $this->entityManager
            ->getRepository(Reservation::class)
            ->createQueryBuilder('r')
            ->where('r.provider = :provider')
            ->andWhere('r.reservationFrom <= :to')
            ->andWhere('r.reservationTo >= :from')
            ->setParameter('provider', $provider)
            ->setParameter('from', $reservationFrom)
            ->setParameter('to', $reservationTo)
            ->getQuery()
            ->getResult();


        if (!empty($existingReservations)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
