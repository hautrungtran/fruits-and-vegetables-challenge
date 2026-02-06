<?php

namespace App\Controller;

use App\Entity\Produce;
use App\Form\ProduceFilterType;
use App\Repository\ProduceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class ProduceController extends AbstractController
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly NormalizerInterface $serializer,
    ) {
    }

    /**
     * @template T of Produce
     *
     * @param ProduceRepository<T> $repository
     */
    protected function listItems(
        Request $request,
        ProduceRepository $repository,
    ): JsonResponse {
        $form = $this->createForm(ProduceFilterType::class);
        $form->submit($request->query->all(), false);
        if (!$form->isValid()) {
            return $this->json(
                ['errors' => $this->serializer->normalize($form->getErrors(true, true), 'json')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $name = $form->get('name')->getData();
        $quantityFrom = $form->get('quantityFrom')->getData();
        $quantityTo = $form->get('quantityTo')->getData();
        $unit = $form->get('unit')->getData();

        $items = $repository->filter(
            $name ?: null,
            $quantityFrom,
            $quantityTo,
        );

        return $this->json($this->serializer->normalize($items, 'json', [
            'unit' => $unit,
        ]));
    }

    protected function showItem(Produce $produce): JsonResponse
    {
        return $this->json($this->serializer->normalize($produce, 'json'));
    }

    protected function createItem(Request $request, Produce $produce, string $formType): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            return $this->json(['error' => 'Invalid JSON: '.$exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        if (!is_array($data)) {
            return $this->json(['error' => 'JSON payload must be an object.'], Response::HTTP_BAD_REQUEST);
        }

        $form = $this->createForm($formType, $produce);
        $form->submit($data, true);
        if (!$form->isValid()) {
            return $this->json(
                ['errors' => $this->serializer->normalize($form->getErrors(true, true), 'json')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->entityManager->persist($produce);
        $this->entityManager->flush();

        return $this->json($this->serializer->normalize($produce, 'json'), Response::HTTP_CREATED);
    }

    protected function deleteItem(Produce $produce): JsonResponse
    {
        $this->entityManager->remove($produce);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
