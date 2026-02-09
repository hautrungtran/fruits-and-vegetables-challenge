<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Fruit;
use App\Entity\Vegetable;
use App\Enum\Unit;
use App\Form\ProduceRowType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class ProduceImporter
{
    private const BATCH_SIZE = 50;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    /**
     * @return array{created:int, skipped:int, warnings:string[]}
     */
    public function importFromFile(string $file): array
    {
        if (!is_file($file)) {
            throw new \RuntimeException(sprintf('File not found: %s', $file));
        }

        try {
            $payload = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \RuntimeException(sprintf('Invalid JSON: %s', $exception->getMessage()), 0, $exception);
        }

        if (!is_array($payload)) {
            throw new \RuntimeException('JSON payload must be an array of items.');
        }

        $created = 0;
        $skipped = 0;
        $warnings = [];

        foreach ($payload as $index => $row) {
            if (!is_array($row)) {
                $warnings[] = sprintf('Skipping row %d: invalid structure.', $index + 1);
                ++$skipped;
                continue;
            }

            $form = $this->formFactory->create(ProduceRowType::class);
            $form->submit($row);

            if (!$form->isValid()) {
                $warnings[] = sprintf(
                    'Skipping row %d: %s',
                    $index + 1,
                    $this->formatFormErrors($form)
                );
                ++$skipped;
                continue;
            }

            $type = $form->get('type')->getData();
            $entity = 'fruit' === $type ? new Fruit() : new Vegetable();

            $unitEnum = $form->get('unit')->getData();
            if (!$unitEnum instanceof Unit) {
                $warnings[] = sprintf('Skipping row %d: unknown unit.', $index + 1);
                ++$skipped;
                continue;
            }

            $entity
                ->setName((string) $form->get('name')->getData())
                ->setQuantity((int) $form->get('quantity')->getData())
                ->setUnit($unitEnum);

            $this->entityManager->persist($entity);
            ++$created;

            if (0 === $created % self::BATCH_SIZE) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        $this->entityManager->flush();

        return [
            'created' => $created,
            'skipped' => $skipped,
            'warnings' => $warnings,
        ];
    }

    private function formatFormErrors(FormInterface $form): string
    {
        $messages = [];

        foreach ($form->getErrors(true, true) as $error) {
            if (!$error instanceof FormError) {
                continue;
            }

            $origin = $error->getOrigin();
            $field = $origin instanceof FormInterface ? $origin->getName() : 'form';
            $messages[] = sprintf('%s: %s', $field, $error->getMessage());
        }

        return implode('; ', $messages);
    }
}
