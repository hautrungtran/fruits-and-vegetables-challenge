<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Fruit;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ProduceRepository<Fruit>
 */
class FruitRepository extends ProduceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fruit::class);
    }
}
