<?php

namespace App\Repository;

use App\Entity\CompanyUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompanyUser>
 *
 * @method CompanyUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyUser[]    findAll()
 * @method CompanyUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyUser::class);
    }

}
