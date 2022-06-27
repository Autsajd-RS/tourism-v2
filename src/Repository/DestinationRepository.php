<?php

namespace App\Repository;

use App\Entity\Destination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

        if (in_array($criteria['popularity'], ['ASC', 'DESC'], true)) {
            $builder->addOrderBy('d.popularity', $criteria['popularity']);
        }

        if (in_array($criteria['attendance'], ['ASC', 'DESC'], true)) {
            $builder->addOrderBy('d.attendance', $criteria['attendance']);
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

        /*if (
            !$criteria['categoryId'] &&
            !$criteria['cityId'] &&
            !$criteria['name'] &&
            !$criteria['popularity'] &&
            !$criteria['attendance']
        ) {
            return $this->list();
        }*/

        $paginator = new Paginator($builder->getQuery());

        $result['totalResults'] = $paginator->count();
        $result['page'] = $page;
        $result['pagesCount'] = ceil($result['totalResults'] / $limit);
        $result['items'] = $paginator->getIterator()->getArrayCopy();

        return $result;
    }
}
