<?php

namespace Bolt\Extension\Bolt\MarketPlace\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;

/**
 * A password reset form class
 *
 * @author Ross Riley, riley.ross@gmail.com
 */
class ResetPasswordForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'password', 
                'repeated', [
                    'type'           => Type\PasswordType::class,
                    'required'       => true,
                    'first_options'  => ['label' => 'Choose a secure password'],
                    'second_options' => ['label' => 'Repeat Password'],
                ]
            )
            ->add('reset',   Type\SubmitType::class,   ['label' => 'Reset Password']);
    }

    public function getName()
    {
        return 'passwordupdate';
    }
}
