<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Category;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'row_attr' => ['class' => 'event-form-row'],
            ])
            ->add('place', TextType::class, [
                'row_attr' => ['class' => 'event-form-row'],
            ])
            ->add('startDate', DateTimeType::class, [
                'row_attr' => ['class' => 'event-form-row'],
            ])
            ->add('endDate', DateTimeType::class, [
                'row_attr' => ['class' => 'event-form-row'],
            ])
            ->add('isPrivate', CheckboxType::class, [
                'row_attr' => ['class' => 'event-form-row'],
            ])
            ->add('isFinancialParticipation', CheckboxType::class, [
                'required' => false,
                'row_attr' => ['class' => 'event-form-row'],
                'attr' => ['id' => 'event_isFinancialParticipation'], // Ajoutez l'ID
            ])
            ->add('financialParticipationAmount', TextType::class, [
                'label' => false,
                'required' => false,
                'row_attr' => ['class' => 'event-form-row'],
                'attr' => ['id' => 'event_financialParticipationAmount'], // Ajoutez l'ID
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'row_attr' => ['class' => 'event-form-row'],
                'choice_label' => 'name',
                'placeholder' => 'Select a category', // Texte par défaut pour le champ (optionnel)
                'multiple' => false, // Liste déroulante (par défaut)
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
