<?php

namespace App\Serializer;

use App\Entity\Produce;
use App\Enum\Unit;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProduceNormalizer implements NormalizerInterface
{
    /**
     * @param Produce $object
     *
     * @return array{id:int|null, name:string|null, quantity:int|float|null, unit:string|null}
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $quantity = $object->getQuantity();
        $unit = $object->getUnit();
        $target = $context['unit'] ?? null;

        $targetUnit = $target instanceof Unit
            ? $target
            : (is_string($target) ? Unit::tryFrom($target) : null);

        if (null !== $quantity && $unit instanceof Unit && $targetUnit instanceof Unit && $unit !== $targetUnit) {
            if (Unit::G === $unit && Unit::KG === $targetUnit) {
                $quantity = $quantity / 1000;
            } elseif (Unit::KG === $unit && Unit::G === $targetUnit) {
                $quantity = $quantity * 1000;
            }
        }

        $unitValue = null !== $targetUnit ? $targetUnit->value : $unit?->value;

        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'quantity' => $quantity,
            'unit' => $unitValue,
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Produce;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Produce::class => true,
        ];
    }
}
