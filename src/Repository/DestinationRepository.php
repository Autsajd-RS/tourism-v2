<?php

namespace App\Repository;

use App\Entity\Destination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Webmozart\Assert\Tests\StaticAnalysis\null;

/**
 * @extends ServiceEntityRepository<Destination>
 *
 * @method Destination|null find($id, $lockMode = null, $lockVersion = null)
 * @method Destination|null findOneBy(array $criteria, array $orderBy = null)
 * @method Destination[]    findAll()
 * @method Destination[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DestinationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Destination::class);
    }

    public function add(Destination $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Destination $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Destination[]
     */
    public function list(): array
    {
        return $this->createQueryBuilder('d')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Destination[]
     */
    public function searchByCityAndCategory(?int $cityId = null, ?int $categoryId = null): array
    {
        $builder = $this->createQueryBuilder('d');

        if ($cityId !== null) {
            $builder->andWhere('d.city = :cityId')->setParameter('cityId', $cityId);
        }

        if ($categoryId !== null) {
            $builder->andWhere('d.category = :categoryId')->setParameter('categoryId', $categoryId);
        }

        if (!$categoryId && !$cityId) {
            return $this->list();
        }

        return $builder->getQuery()->getResult();
    }
}
