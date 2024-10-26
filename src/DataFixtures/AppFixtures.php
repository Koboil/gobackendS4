<?php

namespace App\DataFixtures;

use App\Entity\Reservation;
use App\Entity\ReservationStatus;
use App\Entity\Review;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture implements FixtureGroupInterface
{
    protected $parameterBag;
    protected $filesystem;

    public function __construct(SluggerInterface $slugger, ParameterBagInterface $parameterBag, Filesystem $filesystem)
    {
        $this->parameterBag = $parameterBag;
        $this->filesystem = $filesystem;
    }

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        if ($this->fileUploader == null) {
            return;
        }
        $faker = Faker\Factory::create();

        $users = $manager->getRepository(User::class)->findAll();
        $services = $manager->getRepository(Service::class)->findAll();
        // Reviews
        for ($i = 0; $i < 30; $i++) {
            $review = $this->createReview(
                $faker->randomElement($users),
                $faker->randomElement($services),
                $faker->numberBetween(1, 5),
                $faker->realTextBetween(100, 2000)
            );
            $manager->persist($review);
        }
        $manager->flush();

        /*
         * Reservations
         */
        $rawReservationStatus = [
            ['draft', 'yellow'],
            ['confirmed', 'green'],
            ['canceled', 'orange'],
            ['completed', 'gray'],
            ['unavailable', 'red'],
        ];
        $reservationStatus = [];

        foreach ($rawReservationStatus as $key => $value) {
            $status = new ReservationStatus();
            $status->setStatus($value[0]);
            $status->setColor($value[1]);
            $manager->persist($status);
            $reservationStatus[] = $status;
        }
        $manager->flush();

        $rawReservations = [
            [$users[0], $services[0], $reservationStatus[0]],
            [$users[0], $services[1], $reservationStatus[1]],
            [$users[0], $services[2], $reservationStatus[2]],
        ];
        foreach ($rawReservations as $key => $value) {
            $reservation = new Reservation();
            $reservation->setUser($value[0]);
            $reservation->setService($value[1]);
            $reservation->setStatus($value[2]);

            $startsAt = \DateTimeImmutable::createFromMutable(($faker->dateTimeBetween('+1 week', '+2 week')));
            $endsAt = clone $startsAt;
            $endsAt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween($startsAt, $endsAt->modify('+4 week')));
            $reservation->setStartAt($startsAt);
            $reservation->setEndAt($endsAt);

            $manager->persist($reservation);
        }


    }

    public function createReview(User $customer, Service $service, ?int $stars = 1, ?string $text = null): Review
    {
        $review = new Review();
        $review->setUser($customer)->setService($customer)->setStars($stars)->setText($text);
        return $review;
    }

}
