<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Vegetable;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VegetableType extends ProduceType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vegetable::class,
        ]);
    }
}
