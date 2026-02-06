<?php

namespace App\Repository;

use App\Entity\Vegetable;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ProduceRepository<Vegetable>
 */
class VegetableRepository extends ProduceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vegetable::class);
    }
}
