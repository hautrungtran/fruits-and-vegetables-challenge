<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template T of object
 *
 * @extends ServiceEntityRepository<T>
 */
abstract class ProduceRepository extends ServiceEntityRepository
{
    /**
     * @param class-string<T> $entityClass
     */
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @return array<object>
     */
    public function filter(?string $name, ?int $quantityFrom, ?int $quantityTo): array
    {
        $builder = $this->createQueryBuilder('e');

        if (null !== $name && '' !== $name) {
            $builder
                ->andWhere('e.name LIKE :name')
                ->setParameter('name', '%'.$name.'%');
        }

        if (null !== $quantityFrom) {
            $builder
                ->andWhere('e.quantity >= :quantityFrom')
                ->setParameter('quantityFrom', $quantityFrom);
        }

        if (null !== $quantityTo) {
            $builder
                ->andWhere('e.quantity <= :quantityTo')
                ->setParameter('quantityTo', $quantityTo);
        }

        return $builder->getQuery()->getResult();
    }
}
