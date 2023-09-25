<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Category;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
                'label' => 'Nom de l\'événement',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'row_attr' => ['class' => 'event-form-row'],
                'choice_label' => 'name',
                'placeholder' => 'Select a category', // Texte par défaut pour le champ (optionnel)
                'multiple' => false, // Liste déroulante (par défaut)
                'label' => 'Catégorie',
            ])
            ->add('place', TextType::class, [
                'row_attr' => ['class' => 'event-form-row'],
                'label' => 'Lieu / Adresse',
            ])
            ->add('startDate', DateTimeType::class, [
                'row_attr' => ['class' => 'event-form-row'],
                'label' => 'Début',
            ])
            ->add('endDate', DateTimeType::class, [
                'row_attr' => ['class' => 'event-form-row'],
                'label' => 'Fin',
            ])
            ->add('isPrivate', CheckboxType::class, [
                'row_attr' => ['class' => 'event-form-row'],
                'label' => 'Cet événement est-il privé ?',
            ])
            ->add('isFinancialParticipation', CheckboxType::class, [
                'required' => false,
                'row_attr' => ['class' => 'event-form-row'],
                'attr' => ['id' => 'event_isFinancialParticipation'], // Ajoutez l'ID
                'label' => 'Y-a-t-il une participation financière ?',
            ])
            ->add('financialParticipationAmount', TextType::class, [
                'label' => false,
                'required' => false,
                'row_attr' => ['class' => 'event-form-row'],
                'attr' => ['id' => 'event_financialParticipationAmount'], // Ajoutez l'ID
                'label' => 'Montant de la participation',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Créer un nouvel événement',
                'attr' => ['class' => 'btn btn-form'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
