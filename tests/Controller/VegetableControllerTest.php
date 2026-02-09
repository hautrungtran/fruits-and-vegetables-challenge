<?php

namespace App\Tests\Controller;

use App\Entity\Vegetable;
use App\Enum\Unit;
use App\Tests\FunctionalTestCase;

class VegetableControllerTest extends FunctionalTestCase
{
    public function testIndexFiltersByQuantityRange(): void
    {
        $carrot = (new Vegetable())
            ->setName('Carrot')
            ->setQuantity(2500)
            ->setUnit(Unit::G);
        $celery = (new Vegetable())
            ->setName('Celery')
            ->setQuantity(500)
            ->setUnit(Unit::G);

        $this->entityManager->persist($carrot);
        $this->entityManager->persist($celery);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/vegetables', [
            'quantityFrom' => 1000,
            'quantityTo' => 3000,
        ]);

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true);

        self::assertCount(1, $payload);
        self::assertSame('Carrot', $payload[0]['name']);
    }

    public function testShowReturnsVegetable(): void
    {
        $vegetable = (new Vegetable())
            ->setName('Tomato')
            ->setQuantity(1200)
            ->setUnit(Unit::G);

        $this->entityManager->persist($vegetable);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/vegetables/'.$vegetable->getId());

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Tomato', $payload['name']);
    }

    public function testCreatePersistsVegetable(): void
    {
        $this->client->request(
            'POST',
            '/api/vegetables',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Beans',
                'quantity' => 500,
                'unit' => 'g',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Beans', $payload['name']);

        self::assertSame(1, $this->entityManager->getRepository(Vegetable::class)->count([]));
    }

    public function testCreateRejectsInvalidQuantity(): void
    {
        $this->client->request(
            'POST',
            '/api/vegetables',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Beans',
                'quantity' => 0,
                'unit' => 'g',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(422);
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('errors', $payload);
        self::assertArrayHasKey('quantity', $payload['errors']);
        self::assertSame(0, $this->entityManager->getRepository(Vegetable::class)->count([]));
    }

    public function testDeleteRemovesVegetable(): void
    {
        $vegetable = (new Vegetable())
            ->setName('Onion')
            ->setQuantity(100)
            ->setUnit(Unit::G);

        $this->entityManager->persist($vegetable);
        $this->entityManager->flush();

        $this->client->request('DELETE', '/api/vegetables/'.$vegetable->getId());

        self::assertResponseStatusCodeSame(204);
        self::assertSame(0, $this->entityManager->getRepository(Vegetable::class)->count([]));
    }
}
