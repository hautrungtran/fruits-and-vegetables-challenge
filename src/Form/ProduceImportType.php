<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProduceImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('file', FileType::class, [
            'constraints' => [
                new NotBlank(),
                new File(mimeTypes: [
                    'application/json',
                    'text/json',
                    'text/plain',
                ], mimeTypesMessage: 'Please upload a valid JSON file.'),
            ],
        ]);
    }
}
