<?php

namespace App\Repository;

use App\Entity\Destination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException as NonUniqueResultExceptionAlias;
use Doctrine\ORM\NoResultException as NoResultExceptionAlias;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
     * @throws \Exception
     */
    public function searchByCriteria(array $criteria): array
    {
        $builder = $this->createQueryBuilder('d');

        if ($criteria['cityId'] !== null) {
            if (is_array($criteria['cityId'])) {
                $builder->andWhere('d.city IN (:cityId)')->setParameter('cityId', $criteria['cityId']);
            } else {
                $builder->andWhere('d.city = :cityId')->setParameter('cityId', $criteria['cityId']);
            }
        }

        if ($criteria['categoryId'] !== null) {
            if (is_array($criteria['categoryId'])) {
                $builder->andWhere('d.category IN (:categoryId)')->setParameter('categoryId', $criteria['categoryId']);
            } else {
                $builder->andWhere('d.category = :categoryId')->setParameter('categoryId', $criteria['categoryId']);
            }
        }

        if ($criteria['name'] !== null) {
            $builder->andWhere('d.name LIKE :name')->setParameter('name', '%' . $criteria['name'] . '%');
        }

        $builder->orderBy('d.id');

        if (in_array($criteria['sort'], ['popularity', 'attendance'], true)) {
            if ($criteria['sort'] === 'popularity') {
                $builder->orderBy('d.popularity', 'DESC');
            }

            if ($criteria['sort'] === 'attendance') {
                $builder->orderBy('d.attendance', 'DESC');
            }
        }

        $limit = 10;
        if ($criteria['limit'] && is_int($criteria['limit']) && $criteria['limit'] > 0) {
            $limit = $criteria['limit'];
        }

        $page = 1;
        if ($criteria['page'] && is_int($criteria['page']) && $criteria['page'] > 0) {
            $page = $criteria['page'];
        }

        $builder->setFirstResult(($page - 1) * $limit);
        $builder->setMaxResults($limit);


        $paginator = new Paginator($builder->getQuery());

        $result['totalResults'] = $paginator->count();
        $result['page'] = $page;
        $result['pagesCount'] = ceil($result['totalResults'] / $limit);
        $result['items'] = $paginator->getIterator()->getArrayCopy();

        return $result;
    }

    public function findCount()
    {
        try {
            return $this->createQueryBuilder('d')
                ->select('COUNT(d.id) total')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultExceptionAlias|NonUniqueResultExceptionAlias $e) {
            return 0;
        }
    }

    public function findTopPopular()
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.popularity', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

    public function findTopAttended()
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.attendance', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }
}
