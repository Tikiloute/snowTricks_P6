<?php

namespace App\Repository;

use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Trick|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trick|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trick[]    findAll()
 * @method Trick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trick::class);
    }

    /**
     * return all tricks with paging
     *
     */
    public function getPaginateTricks($page, $limit, $filters = null)
    {

        if ($filters != null){
            return $this->createQueryBuilder('t')
            ->andWhere('t.category IN(:cats)')
            ->setParameter(':cats', $filters)
            ->orderBy('t.id', 'ASC')
            ->setFirstResult(($page * $limit) - $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        } else {

            return $this->createQueryBuilder('t')
            ->orderBy('t.id', 'ASC')
            ->setFirstResult(($page * $limit) - $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        }
        
    }

    /**
     * Get trick by 10 (for hompage)
     */
    public function getTrickHomePage()
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.id', 'ASC')
            ->setFirstResult(0)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    /**
     * get total of tricks
     */
    public function getTotalTricks($filter = null)
    {
        if ($filter !== null){

            return $this->createQueryBuilder('t')
            ->select("COUNT('t')")
            ->andWhere('t.category = :cat')
            ->setParameter(':cat', $filter)
            ->getQuery()
            ->getSingleScalarResult();

        } else {

            return $this->createQueryBuilder('t')
            ->select("COUNT('t')")
            ->getQuery()
            ->getSingleScalarResult();
        }
        
        
    }

    public function findBySearch($search)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.name LIKE :val')
            ->setParameter('val', '%'.$search.'%')
            ->getQuery()
            ->getResult()
        ;
    }

    //  * @return Trick[] Returns an array of Trick objects
    //  */
    
    public function findByCategory($category = null)
    {
        if($category != null ){
            
            return $this->createQueryBuilder('t')
            ->andWhere('t.category = :val')
            ->setParameter('val', $category)
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
        } else {

            return $this->createQueryBuilder('t')
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
        }
        
    }
    

    // /**
    //  * @return Trick[] Returns an array of Trick objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Trick
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
