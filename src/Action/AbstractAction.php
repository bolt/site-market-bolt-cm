<?php
namespace Bolt\Extensions\Action;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormError;

use Doctrine\ORM\EntityManager;
use Twig_Environment;
use Aura\Router\Router;

class AbstractAction
{
    
    public $renderer;
    public $forms;
    public $em;
    public $router;

    public function __construct(Twig_Environment $renderer, FormFactory $forms, EntityManager $em = null, Router $router = null)
    {
        $this->renderer = $renderer;
        $this->em = $em;
        $this->forms = $forms;
        $this->router = $router;
    }
}