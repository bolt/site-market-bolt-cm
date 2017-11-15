<?php

namespace Bundle\Site\MarketPlace\Form;

use Bundle\Site\MarketPlace\Form\Validator\Constraint\UniqueSourceUrl;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Package form type.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class PackageForm extends AbstractType
{
    /** @var UniqueSourceUrl */
    private $uniqueSourceUrl;

    /**
     * Constructor.
     *
     * @param UniqueSourceUrl $uniqueSourceUrl
     */
    public function __construct(UniqueSourceUrl $uniqueSourceUrl)
    {
        $this->uniqueSourceUrl = $uniqueSourceUrl;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'title',
                Type\TextType::class,
                [
                    'label' => 'Name of extension',
                    'attr'  => [
                        'placeholder' => 'Main title eg: My Widget Extension',
                    ],
                ]
            )
            ->add('source',
                Type\TextType::class,
                [
                    'label' => 'Link to a public Git repository',
                    'attr'  => [
                        'placeholder' => 'eg: https://github.com/you/bolt-widget-extension',
                    ],
                    'constraints' => [
                        $this->uniqueSourceUrl,
                    ],
                ]
            )
            ->add(
                'description',
                Type\TextareaType::class,
                [
                    'label' => 'Description of your extension',
                    'attr'  => [
                        'placeholder' => 'Write a description of your package',
                    ],
                ]
            )
            ->add(
                'submit',
                Type\SubmitType::class,
                [
                    'label' => 'Submit Your Extension',
                ]
            )
        ;
    }

    public function getName()
    {
        return 'package';
    }
}
