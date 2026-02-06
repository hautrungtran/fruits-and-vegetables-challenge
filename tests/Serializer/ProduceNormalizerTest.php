<?php

namespace App\Tests\Serializer;

use App\Entity\Fruit;
use App\Enum\Unit;
use App\Serializer\ProduceNormalizer;
use PHPUnit\Framework\TestCase;

class ProduceNormalizerTest extends TestCase
{
    public function testNormalizeConvertsToRequestedUnit(): void
    {
        $produce = (new Fruit())
            ->setName('Apples')
            ->setQuantity(2000)
            ->setUnit(Unit::G);

        $normalizer = new ProduceNormalizer();
        $normalized = $normalizer->normalize($produce, 'json', ['unit' => 'kg']);

        self::assertSame([
            'id' => null,
            'name' => 'Apples',
            'quantity' => 2,
            'unit' => 'kg',
        ], $normalized);
    }

    public function testNormalizeKeepsOriginalUnitWhenNoContext(): void
    {
        $produce = (new Fruit())
            ->setName('Apples')
            ->setQuantity(500)
            ->setUnit(Unit::G);

        $normalizer = new ProduceNormalizer();
        $normalized = $normalizer->normalize($produce);

        self::assertSame([
            'id' => null,
            'name' => 'Apples',
            'quantity' => 500,
            'unit' => 'g',
        ], $normalized);
    }
}
