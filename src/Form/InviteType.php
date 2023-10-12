<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('users', EntityType::class, [
                'label' => 'Qui sont vos invités ?',
                'class' => User::class,
                'choice_label' => 'email',
                'multiple' => true,
                'row_attr' => [
                    'class' => 'invite-form-row',
                ],
                'attr' => [
                    'class' => 'user-select js-select2',
                    'placeholder' => 'Entrez l\'email de l\'utilisateur à inviter',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer les invitations',
                'attr' => ['class' => 'btn btn-form']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null, 
        ]);
    }
}
