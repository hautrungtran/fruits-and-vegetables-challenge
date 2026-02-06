<?php

namespace App\Form;

use App\Entity\Fruit;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FruitType extends ProduceType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Fruit::class,
        ]);
    }
}
