<?php

namespace App\Tests\Service;

use App\Enum\Unit;
use App\Form\ProduceRowType;
use App\Service\ProduceImporter;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class ProduceImporterTest extends TestCase
{
    public function testImportFromFileHandlesValidAndInvalidRows(): void
    {
        $payload = [
            [
                'name' => 'Apples',
                'type' => 'fruit',
                'quantity' => 2,
                'unit' => 'kg',
            ],
            [
                'name' => 'Carrot',
                'type' => 'vegetable',
                'quantity' => 500,
                'unit' => 'g',
            ],
            [
                'name' => '',
                'type' => 'fruit',
                'quantity' => 1,
                'unit' => 'g',
            ],
            [
                'name' => 'BadUnit',
                'type' => 'fruit',
                'quantity' => 1,
                'unit' => 'lb',
            ],
        ];

        $file = tempnam(sys_get_temp_dir(), 'produce_');
        file_put_contents($file, json_encode($payload, JSON_THROW_ON_ERROR));

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::exactly(2))->method('persist');
        $entityManager->expects(self::once())->method('flush');
        $entityManager->expects(self::never())->method('clear');

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects(self::exactly(4))
            ->method('create')
            ->with(ProduceRowType::class)
            ->willReturnOnConsecutiveCalls(
                $this->createValidForm('fruit', 'Apples', 2, Unit::KG),
                $this->createValidForm('vegetable', 'Carrot', 500, Unit::G),
                $this->createInvalidForm('name', 'Name is required.'),
                $this->createInvalidForm('unit', 'Invalid unit.')
            );

        $importer = new ProduceImporter($entityManager, $formFactory);
        $result = $importer->importFromFile($file);

        self::assertSame(2, $result['created']);
        self::assertSame(2, $result['skipped']);
        self::assertCount(2, $result['warnings']);
    }

    private function createValidForm(string $type, string $name, int $quantity, Unit $unit): FormInterface
    {
        $form = $this->createMock(FormInterface::class);
        $form->method('submit');
        $form->method('isValid')->willReturn(true);
        $form->method('getErrors')->willReturn(new FormErrorIterator($form, []));

        $typeField = $this->createMock(FormInterface::class);
        $typeField->method('getData')->willReturn($type);

        $nameField = $this->createMock(FormInterface::class);
        $nameField->method('getData')->willReturn($name);

        $quantityField = $this->createMock(FormInterface::class);
        $quantityField->method('getData')->willReturn($quantity);

        $unitField = $this->createMock(FormInterface::class);
        $unitField->method('getData')->willReturn($unit);

        $form->method('get')->willReturnMap([
            ['type', $typeField],
            ['name', $nameField],
            ['quantity', $quantityField],
            ['unit', $unitField],
        ]);

        return $form;
    }

    private function createInvalidForm(string $fieldName, string $message): FormInterface
    {
        $form = $this->createMock(FormInterface::class);
        $form->method('submit');
        $form->method('isValid')->willReturn(false);

        $field = $this->createMock(FormInterface::class);
        $field->method('getName')->willReturn($fieldName);

        $error = new FormError($message);
        $error->setOrigin($field);

        $form->method('getErrors')->willReturn(new FormErrorIterator($form, [$error]));

        return $form;
    }
}
