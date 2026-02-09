<?php

namespace App\Tests\Controller;

use App\Entity\Fruit;
use App\Enum\Unit;
use App\Tests\FunctionalTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportControllerTest extends FunctionalTestCase
{
    public function testImportCreatesEntitiesFromUpload(): void
    {
        $sourceFile = dirname(__DIR__, 2).'/request.json';
        $tmpFile = tempnam(sys_get_temp_dir(), 'produce_');
        copy($sourceFile, $tmpFile);

        $file = new UploadedFile(
            $tmpFile,
            'produce.json',
            'application/json',
            null,
            true
        );

        $this->client->request('POST', '/api/import', files: ['file' => $file]);

        self::assertResponseIsSuccessful();
        $response = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame(20, $response['created']);
        self::assertSame(0, $response['skipped']);

        $fruit = $this->entityManager->getRepository(Fruit::class)->findOneBy(['name' => 'Apples']);
        self::assertNotNull($fruit);
        self::assertSame(20000, $fruit->getQuantity());
        self::assertSame(Unit::G, $fruit->getUnit());
    }
}
