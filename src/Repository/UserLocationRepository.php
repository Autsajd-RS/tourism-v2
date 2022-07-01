<?php

namespace App\Repository;

use App\Entity\UserLocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserLocation>
 *
 * @method UserLocation|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserLocation|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserLocation[]    findAll()
 * @method UserLocation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserLocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserLocation::class);
    }

    public function add(UserLocation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserLocation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findSimilar(UserLocation $location)
    {
        return $this->createQueryBuilder('ul')
            ->where('ul.userId = :userId')
            ->andWhere('ul.latitude LIKE :lat')
            ->andWhere('ul.longitude LIKE :long')
            ->setParameters([
                'userId' => $location->getUserId(),
                'lat' => '%' . substr($location->getLatitude(), 0, 7) . '%',
                'long' => '%' . substr($location->getLongitude(), 0, 7) . '%'
            ])
            ->getQuery()
            ->getResult();
    }
}
