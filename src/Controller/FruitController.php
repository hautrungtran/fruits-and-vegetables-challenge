<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Fruit;
use App\Form\FruitType;
use App\Repository\FruitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FruitController extends ProduceController
{
    public function __construct(
        private readonly FruitRepository $repository,
        EntityManagerInterface $entityManager,
        NormalizerInterface $serializer,
    ) {
        parent::__construct($entityManager, $serializer);
    }

    #[Route('/fruits', name: 'app_fruit_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        return $this->listItems($request, $this->repository);
    }

    #[Route('/fruits/{id}', name: 'app_fruit_show', methods: ['GET'])]
    public function show(#[MapEntity] Fruit $fruit): JsonResponse
    {
        return $this->showItem($fruit);
    }

    #[Route('/fruits', name: 'app_fruit_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->createItem($request, new Fruit(), FruitType::class);
    }

    #[Route('/fruits/{id}', name: 'app_fruit_delete', methods: ['DELETE'])]
    public function delete(#[MapEntity] Fruit $fruit): JsonResponse
    {
        return $this->deleteItem($fruit);
    }
}
