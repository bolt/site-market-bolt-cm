<?php
/**
 * A password reset form class
 *
 * @author Ross Riley, riley.ross@gmail.com
 */


namespace Bolt\Extension\Bolt\MarketPlace\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ResetForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("email",      'text',     ['label'=>"Your email address"])
            ->add('submit',     'submit',   ['label'=>"Send Reset Request"]);
    }

    public function getName()
    {
        return 'passwordreset';
    }
}