<?php

namespace App\Tests\Serializer;

use App\Serializer\FormErrorIteratorNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;

class FormErrorIteratorNormalizerTest extends TestCase
{
    public function testNormalizeGroupsErrorsByField(): void
    {
        $normalizer = new FormErrorIteratorNormalizer();

        $nameField = $this->createMock(FormInterface::class);
        $nameField->method('getName')->willReturn('name');

        $quantityField = $this->createMock(FormInterface::class);
        $quantityField->method('getName')->willReturn('quantity');

        $error1 = new FormError('Name is required.');
        $error1->setOrigin($nameField);

        $error2 = new FormError('Quantity is required.');
        $error2->setOrigin($quantityField);

        $error3 = new FormError('Name must be longer.');
        $error3->setOrigin($nameField);

        $iterator = new FormErrorIterator($nameField, [$error1, $error2, $error3]);

        $normalized = $normalizer->normalize($iterator);

        self::assertSame([
            'name' => ['Name is required.', 'Name must be longer.'],
            'quantity' => ['Quantity is required.'],
        ], $normalized);
    }
}
