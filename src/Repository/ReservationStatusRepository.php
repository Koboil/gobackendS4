<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\ReservationStatus;

/**
 * @extends ServiceEntityRepository<ReservationStatus>
 *
 * @method ReservationStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReservationStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReservationStatus[]    findAll()
 * @method ReservationStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationStatus::class);
    }

    public function add(ReservationStatus $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ReservationStatus $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
