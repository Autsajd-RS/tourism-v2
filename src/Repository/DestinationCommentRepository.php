<?php

namespace App\Repository;

use App\Entity\DestinationComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DestinationComment>
 *
 * @method DestinationComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method DestinationComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method DestinationComment[]    findAll()
 * @method DestinationComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DestinationCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DestinationComment::class);
    }

    public function add(DestinationComment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DestinationComment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
