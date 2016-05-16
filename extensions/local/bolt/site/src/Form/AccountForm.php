<?php
namespace Bolt\Extension\Bolt\MarketPlace\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AccountForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email',    'text',     ['label' => 'Your email address'])
            ->add('name',     'text',     ['label' => 'Your name'])
            ->add('username', 'text',     ['label' => 'Username: This will be the prefix to your submitted packages.'])
            ->add('password', 'repeated', [
                'type'           => 'password',
                'required'       => true,
                'first_options'  => ['label' => 'Choose a secure password'],
                'second_options' => ['label' => 'Repeat Password'],
            ])
            ->add('create',   'submit',   ['label' => 'Create Account'])
            ->add('reset',    'submit',   ['label' => 'Reset Password']);
    }

    public function getName()
    {
        return 'account';
    }
}
