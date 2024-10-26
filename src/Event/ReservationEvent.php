<?php


namespace App\Event;

use App\Entity\Reservation;

class ReservationEvent
{

    const RESERVATION_CUSTOMER_CONFIRM= 'UserEvent.RESERVATION_customer_confirmation_action';
    const RESERVATION_CUSTOMER_HAS_CONFIRMED = 'UserEvent.RESERVATION_customer_confirmed';
    const RESERVATION_PROVIDER_NEW = 'UserEvent.RESERVATION_provider_new';
    const RESERVATION_CUSTOMER_HAS_CANCELED = 'UserEvent.RESERVATION_customer_has_canceled';
    const RESERVATION_PROVIDER_HAS_CANCELED = 'UserEvent.RESERVATION_provider_has_canceled';

    public function __construct(private Reservation $reservation)
    {

    }

    public function getReservation(): Reservation
    {
        return $this->reservation;
    }

    public function setReservation(Reservation $reservation): static
    {
        $this->reservation = $reservation;
        return $this;
    }


}
