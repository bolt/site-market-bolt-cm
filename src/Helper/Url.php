<?php
namespace Bolt\Extensions\Helper;
use Aura\Router\Router;


class Url extends \Twig_Extension
{

    public $router;

    public function __construct(Router $router) {
        $this->router = $router;
    }

    public function getFunctions()
    {
        return array(
            'url'  => new \Twig_Function_Method($this, 'getUrl')
        );
    }


    public function getUrl($path, $options = [])
    {
        return $this->router->generate($path, $options);
    }

    public function getName()
    {
        return 'url_helper';
    }

}
