<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Category;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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
                'label' => 'Nom de l\'événement',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Select a category', 
                'multiple' => false, 
                'label' => 'Catégorie',
            ])
            ->add('place', TextType::class, [
                'label' => 'Lieu / Adresse',
            ])
            ->add('startDate', TextType::class, [
                'attr' => ['class' => 'datetimepicker'],
                'label' => 'Début',
                'mapped' => false,
            ])
            ->add('endDate', TextType::class, [
                'attr' => ['class' => 'datetimepicker'],
                'label' => 'Fin',
                'mapped' => false,
            ])
            ->add('isPrivate', HiddenType::class, [
                'data' => '0',  
            ])
            ->add('isFinancialParticipation', HiddenType::class, [
                'data' => '0',
            ])
            ->add('financialParticipationAmount', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['id' => 'event_financialParticipationAmount'], 
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
