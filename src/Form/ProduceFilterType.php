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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProduceFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
            ])
            ->add('quantityFrom', IntegerType::class, [
                'required' => false,
                'constraints' => [
                    new GreaterThanOrEqual(0),
                ],
            ])
            ->add('quantityTo', IntegerType::class, [
                'required' => false,
                'constraints' => [
                    new GreaterThanOrEqual(0),
                ],
            ])
            ->add('unit', EnumType::class, [
                'required' => false,
                'data' => Unit::G,
                'class' => Unit::class,
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event): void {
            $data = $event->getData();
            if (!is_array($data)) {
                return;
            }

            if (isset($data['name']) && is_string($data['name'])) {
                $normalized = preg_replace('/\s+/', ' ', $data['name']) ?? '';
                $data['name'] = trim($normalized);
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'constraints' => [
                new Callback($this->validateRange(...)),
            ],
        ]);
    }

    public function validateRange(mixed $data, ExecutionContextInterface $context): void
    {
        if (!is_array($data)) {
            return;
        }

        $from = $data['quantityFrom'] ?? null;
        $to = $data['quantityTo'] ?? null;

        if (null === $from || null === $to) {
            return;
        }

        if (is_numeric($from) && is_numeric($to) && (int) $from > (int) $to) {
            $context
                ->buildViolation('quantityFrom must be less than or equal to quantityTo.')
                ->atPath('quantityFrom')
                ->addViolation();
        }
    }
}
