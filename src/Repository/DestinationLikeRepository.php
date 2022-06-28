<?php

namespace App\Repository;

use App\Entity\Destination;
use App\Entity\DestinationLike;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DestinationLike>
 *
 * @method DestinationLike|null find($id, $lockMode = null, $lockVersion = null)
 * @method DestinationLike|null findOneBy(array $criteria, array $orderBy = null)
 * @method DestinationLike[]    findAll()
 * @method DestinationLike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DestinationLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DestinationLike::class);
    }

    public function add(DestinationLike $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DestinationLike $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function isLikedByUser(Destination $destination, User $user)
    {
        return $this->createQueryBuilder('dl')
            ->where('dl.destinationId = :destinationId')
            ->andWhere('dl.userId = :userId')
            ->andWhere('dl.negative = 0')
            ->setParameters(['destinationId' => $destination->getId(), 'userId' => $user->getId()])
            ->orderBy('dl.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }

    public function isDislikedByUser(Destination $destination, User $user)
    {
        return $this->createQueryBuilder('dl')
            ->where('dl.destinationId = :destinationId')
            ->andWhere('dl.userId = :userId')
            ->andWhere('dl.negative = 1')
            ->setParameters(['destinationId' => $destination->getId(), 'userId' => $user->getId()])
            ->orderBy('dl.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }

    public function lastLike(Destination $destination, User $user)
    {
        return $this->createQueryBuilder('dl')
            ->where('dl.destinationId = :destinationId')
            ->andWhere('dl.userId = :userId')
            ->setParameters(['destinationId' => $destination->getId(), 'userId' => $user->getId()])
            ->orderBy('dl.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()[0];
    }

    public function findDestinationLikes(Destination $destination)
    {
        return $this->createQueryBuilder('dl')
            ->where('dl.destinationId = :destinationId')
            ->andWhere('dl.deleted != 0')
            ->setParameter('destinationId', $destination->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function likeCount(Destination $destination)
    {
        return $this->createQueryBuilder('dl')
            ->select('COUNT(dl.id) as likesCount')
            ->where('dl.deleted = 0')
            ->andWhere('dl.negative = 0')
            ->andWhere('dl.destinationId = :destinationId')
            ->setParameters(['destinationId' => $destination->getId()])
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function dislikeCount(Destination $destination)
    {
        return $this->createQueryBuilder('dl')
            ->select('COUNT(dl.id) as dislikesCount')
            ->where('dl.deleted = 0')
            ->andWhere('dl.negative = 1')
            ->andWhere('dl.destinationId = :destinationId')
            ->setParameters(['destinationId' => $destination->getId()])
            ->getQuery()
            ->getSingleScalarResult();
    }
}
