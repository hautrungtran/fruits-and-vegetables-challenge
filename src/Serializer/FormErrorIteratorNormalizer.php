<?php

namespace App\Serializer;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FormErrorIteratorNormalizer implements NormalizerInterface
{
    /**
     * @param FormErrorIterator<FormError> $object
     *
     * @return array<string, string[]>
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $errors = [];

        foreach ($object as $error) {
            $origin = $error->getOrigin();
            $field = $origin instanceof FormInterface ? $origin->getName() : 'form';
            $errors[$field][] = $error->getMessage();
        }

        return $errors;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof FormErrorIterator;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            FormErrorIterator::class => true,
        ];
    }
}
