<?php

namespace App\Tests\Command;

use App\Entity\Fruit;
use App\Enum\Unit;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ImportProduceCommandTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

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

    public function testCommandImportsRequestJson(): void
    {
        $application = new Application(self::$kernel);
        $command = $application->find('app:import-produce');
        $tester = new CommandTester($command);

        $status = $tester->execute([
            '--file' => dirname(__DIR__, 2).'/request.json',
        ]);

        self::assertSame(0, $status);
        self::assertStringContainsString('Imported 20 items, skipped 0.', $tester->getDisplay());

        $fruit = $this->entityManager->getRepository(Fruit::class)->findOneBy(['name' => 'Apples']);
        self::assertNotNull($fruit);
        self::assertSame(20000, $fruit->getQuantity());
        self::assertSame(Unit::G, $fruit->getUnit());
    }
}
