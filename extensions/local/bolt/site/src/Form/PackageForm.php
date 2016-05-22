<?php

namespace Bolt\Extension\Bolt\MarketPlace\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;

class PackageForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'title',
                Type\TextType::class,     
                [
                    'label' => 'Name of extension',
                    'attr'  => [
                        'placeholder' => 'Main title eg: My Widget Extension'
                    ],
                ]
            )
            ->add('source',
                Type\TextType::class,
                [
                    'label' => 'Link to a public Git repository',
                    'attr'  => [
                        'placeholder' => 'eg: https://github.com/you/bolt-widget-extension'
                    ],
                ]
            )
            ->add(
                'description',
                Type\TextareaType::class,
                [
                    'label' => 'Description of your extension', 
                    'attr' => [
                        'placeholder' => 'Write a description of your package'
                    ]
                ]
            )
            ->add(
                'submit',
                Type\SubmitType::class,
                [
                    'label' => 'Submit Your Extension'
                ]
            )
        ;
    }

    public function getName()
    {
        return 'package';
    }
}
