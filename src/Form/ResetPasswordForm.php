<?php
/**
 * A password reset form class
 *
 * @author Ross Riley, riley.ross@gmail.com
 */


namespace Bolt\Extensions\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ResetPasswordForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('password', 'repeated', [
                'type' => 'password',
                'required' => true,
                'first_options'  => array('label' => 'Choose a secure password'),
                'second_options' => array('label' => 'Repeat Password')
            ])
            ->add('reset',       'submit',   ['label'=>"Reset Password"]);


    }

    public function getName()
    {
        return 'passwordupdate';
    }
}