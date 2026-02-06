<?php

namespace App\Tests\Controller;

use App\Entity\Fruit;
use App\Enum\Unit;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FruitControllerTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    public static function setUpBeforeClass(): void
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
        self::ensureKernelShutdown();
    }

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testIndexFiltersAndConvertsUnits(): void
    {
        $apple = (new Fruit())
            ->setName('Apples')
            ->setQuantity(2000)
            ->setUnit(Unit::G);
        $banana = (new Fruit())
            ->setName('Bananas')
            ->setQuantity(500)
            ->setUnit(Unit::G);

        $this->entityManager->persist($apple);
        $this->entityManager->persist($banana);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/fruits', [
            'name' => 'app',
            'quantityFrom' => 1000,
            'unit' => 'kg',
        ]);

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true);

        self::assertCount(1, $payload);
        self::assertSame('Apples', $payload[0]['name']);
        self::assertSame('kg', $payload[0]['unit']);
        self::assertSame(2, $payload[0]['quantity']);
    }

    public function testShowReturnsFruit(): void
    {
        $fruit = (new Fruit())
            ->setName('Pears')
            ->setQuantity(1500)
            ->setUnit(Unit::G);

        $this->entityManager->persist($fruit);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/fruits/'.$fruit->getId());

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Pears', $payload['name']);
        self::assertSame(1500, $payload['quantity']);
        self::assertSame('g', $payload['unit']);
    }

    public function testCreatePersistsFruit(): void
    {
        $this->client->request(
            'POST',
            '/api/fruits',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Melons',
                'quantity' => 3,
                'unit' => 'kg',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Melons', $payload['name']);
        self::assertSame(3000, $payload['quantity']);
        self::assertSame('g', $payload['unit']);

        self::assertSame(1, $this->entityManager->getRepository(Fruit::class)->count([]));
    }

    public function testCreateRejectsMissingName(): void
    {
        $this->client->request(
            'POST',
            '/api/fruits',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'quantity' => 3,
                'unit' => 'kg',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(422);
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('errors', $payload);
        self::assertArrayHasKey('name', $payload['errors']);
        self::assertSame(0, $this->entityManager->getRepository(Fruit::class)->count([]));
    }

    public function testDeleteRemovesFruit(): void
    {
        $fruit = (new Fruit())
            ->setName('Kiwi')
            ->setQuantity(100)
            ->setUnit(Unit::G);

        $this->entityManager->persist($fruit);
        $this->entityManager->flush();

        $this->client->request('DELETE', '/api/fruits/'.$fruit->getId());

        self::assertResponseStatusCodeSame(204);
        self::assertSame(0, $this->entityManager->getRepository(Fruit::class)->count([]));
    }
}
