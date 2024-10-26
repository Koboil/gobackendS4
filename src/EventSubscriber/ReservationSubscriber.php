<?php


namespace App\EventSubscriber;

use App\Event\ReservationEvent;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReservationSubscriber implements EventSubscriberInterface
{

    public function __construct(private MailerInterface $mailer, private TranslatorInterface $translator, private ManagerRegistry $managerRegistry, private string $noReplyEmail)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ReservationEvent::RESERVATION_CUSTOMER_CONFIRM => 'onCustomerConfirm',
            ReservationEvent::RESERVATION_CUSTOMER_HAS_CONFIRMED => 'onCustomerHasConfirmed',
            ReservationEvent::RESERVATION_CUSTOMER_HAS_CANCELED => 'onCustomerHasCancel',
            ReservationEvent::RESERVATION_PROVIDER_HAS_CANCELED => 'onProviderHasCancel',
            ReservationEvent::RESERVATION_PROVIDER_NEW => 'onProviderNew'
        ];
    }

    public function onCustomerConfirm(ReservationEvent $event)
    {
        $reservation = $event->getReservation();

        $email = (new TemplatedEmail())
            ->to(new Address($reservation->getCustomer()->getEmail()))
            ->subject('Confirmer son rendez-vous')
            ->htmlTemplate('emails/reservation/confirm.html.twig')
            ->context([
                'reservation' => $reservation,]);
        $this->mailer->send($email);

    }

    public function onCustomerHasCancel(ReservationEvent$event)
    {
        $reservation = $event->getReservation();

        $email = (new TemplatedEmail())
            ->to(new Address($reservation->getCustomer()->getEmail()))
            ->subject('Annulation du rendez-vous')
            ->htmlTemplate('emails/reservation/customer_has_canceled.html.twig')
            ->context([
                'reservation' => $reservation,]);
        $this->mailer->send($email);
    }
    public function onProviderHasCancel(ReservationEvent$event)
    {
        $reservation = $event->getReservation();

        $email = (new TemplatedEmail())
            ->to(new Address($reservation->getCustomer()->getEmail()))
            ->subject('Annulation du rendez-vous')
            ->htmlTemplate('emails/reservation/provider_has_canceled.html.twig')
            ->context([
                'reservation' => $reservation,]);
        $this->mailer->send($email);
    }

    public function onCustomerHasConfirmed(ReservationEvent $event)
    {
        $reservation = $event->getReservation();

        $email = (new TemplatedEmail())
            ->to(new Address($reservation->getCustomer()->getEmail()))
            ->subject('Confirmation du rendez-vous')
            ->htmlTemplate('emails/reservation/has_confirmed.html.twig')
            ->context([
                'reservation' => $reservation,]);
        $this->mailer->send($email);
    }

    public function onProviderNew(ReservationEvent $event)
    {
        $reservation = $event->getReservation();

        $email = (new TemplatedEmail())
            ->to(new Address($reservation->getProvider()->getEmail()))
            ->subject('Nouveau rendez-vous')
            ->htmlTemplate('emails/reservation/provider_new.html.twig')
            ->context([
                'reservation' => $reservation,
            ]);
        $this->mailer->send($email);

    }
}
