<?php

namespace App\Form;

use App\Enum\Unit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

abstract class ProduceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [new NotBlank()],
            ])
            ->add('quantity', IntegerType::class, [
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(1),
                ],
            ])
            ->add('unit', EnumType::class, [
                'constraints' => [new NotBlank()],
                'class' => Unit::class,
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event): void {
            $data = $event->getData();
            if (!is_array($data)) {
                return;
            }

            $unit = $data['unit'] ?? null;
            $quantity = $data['quantity'] ?? null;
            if ($unit !== Unit::KG->value || !is_numeric($quantity)) {
                return;
            }

            $data['quantity'] = (int) $quantity * 1000;
            $data['unit'] = Unit::G->value;
            $event->setData($data);
        });
    }
}
