<?php
namespace Bolt\Extensions\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AccountForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add("email",          'text',     ['label'=>"Your email address"])
            ->add("name",           'text',     ['label'=>"Your name"])
            ->add('password', 'repeated', [
                'type' => 'password',
                'required' => true,
                'first_options'  => array('label' => 'Choose a secure password'),
                'second_options' => array('label' => 'Repeat Password'),
            ])
            ->add('create',     'submit',   ['label'=>"Create Account"]);


    }

    public function getName()
    {
        return 'account';
    }



}
