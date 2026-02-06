<?php

namespace App\Controller;

use App\Entity\Vegetable;
use App\Form\VegetableType;
use App\Repository\VegetableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VegetableController extends ProduceController
{
    public function __construct(
        private readonly VegetableRepository $repository,
        EntityManagerInterface $entityManager,
        NormalizerInterface $serializer,
    ) {
        parent::__construct($entityManager, $serializer);
    }

    #[Route('/vegetables', name: 'app_vegetable_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        return $this->listItems($request, $this->repository);
    }

    #[Route('/vegetables/{id}', name: 'app_vegetable_show', methods: ['GET'])]
    public function show(#[MapEntity] Vegetable $vegetable): JsonResponse
    {
        return $this->showItem($vegetable);
    }

    #[Route('/vegetables', name: 'app_vegetable_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->createItem($request, new Vegetable(), VegetableType::class);
    }

    #[Route('/vegetables/{id}', name: 'app_vegetable_delete', methods: ['DELETE'])]
    public function delete(#[MapEntity] Vegetable $vegetable): JsonResponse
    {
        return $this->deleteItem($vegetable);
    }
}
