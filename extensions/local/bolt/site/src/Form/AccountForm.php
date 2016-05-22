<?php

namespace Bolt\Extension\Bolt\MarketPlace\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;

class AccountForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'email',
                Type\TextareaType::class,
                [
                    'label' => 'Your email address'
                ]
            )
            ->add(
                'name',
                Type\TextareaType::class,     
                [
                    'label' => 'Your name'
                ]
            )
            ->add(
                'username',
                Type\TextareaType::class,     
                [
                    'label' => 'Username: This will be the prefix to your submitted packages.'
                ]
            )
            ->add(
                'password', 
                Type\RepeatedType::class, 
                [
                    'type'           => Type\PasswordType::class,
                    'required'       => true,
                    'first_options'  => ['label' => 'Choose a secure password'],
                    'second_options' => ['label' => 'Repeat Password'],
                ]
            )
            ->add(
                'create',
                Type\SubmitType::class,   
                [
                    'label' => 'Create Account'
                ]
            )
            ->add(
                'reset',
                Type\SubmitType::class,   
                [
                    'label' => 'Reset Password'
                ]
            )
        ;
    }

    public function getName()
    {
        return 'account';
    }
}
