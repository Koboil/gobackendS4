<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\ProductContent;
use App\Entity\Review;

/**
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
         private PaginatorInterface $paginator,
    ) {
        parent::__construct($registry, Review::class);
    }

/*
    public function findOwnerReviews($user,  $page = 1)
    {
        $query = $this->createQueryBuilder('r');
        $query->innerJoin('r.product', 'p')
            ->innerJoin(ProductContent::class, 'c', 'WITH', 'c.user = :user AND c.product = p')
            ->setParameter('user', $user);

        $productReviewCollection = $query->getQuery()->execute();
        $pagination = $this->paginator->paginate(
            $productReviewCollection,
            $page,
        );
        $results = $this->getFullProducts($this->eshopHelper, $pagination);

        return [$results, $pagination];
    }

    public function findUserReviews($user, $page = 1)
    {
        $reviews = $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();;
        return $this->getFullProducts($this->eshopHelper, $reviews);
    }


    function getFullProducts($helper, $productReviewCollection)
    {
        $ownerResults = [];
        for ($i = 0; $i < count($productReviewCollection); $i++) {
            $product = $productReviewCollection[$i]->getProduct();
            $ownerResults[] = [
                $productReviewCollection[$i],
                $helper->getFullProduct($product),
            ];
        }
        return $ownerResults;
    }
    */

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Review $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Review $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
