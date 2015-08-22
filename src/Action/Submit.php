<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Composer\Package\Loader\ArrayLoader;
use Composer\Package\Loader\ValidatingArrayLoader;
use Composer\Package\Loader\InvalidPackageException;
use Twig_Environment;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactory;
use Bolt\Extensions\Entity;
use Bolt\Extensions\Service\PackageManager;
use Aura\Router\Router;

class Submit 
{
    
    public $renderer;
    public $em;
    public $forms;
    public $packageManager;
    
    public function __construct(Twig_Environment $renderer, EntityManager $em, FormFactory $forms, PackageManager $packageManager, Router $router)
    {
        $this->renderer = $renderer;
        $this->em = $em;
        $this->forms = $forms;
        $this->packageManager = $packageManager;
        $this->router = $router;
    }
    
    public function __invoke(Request $request)
    {

        $error = false;
        $this->accountUser = $request->get('user');
        $entity = new Entity\Package;
        $form = $this->forms->create('package', $entity);
        $form->handleRequest();

        if ($form->isValid()) {
            $package = $form->getData();            
            $package->created = new \DateTime;
            $package->account = $this->accountUser;
            if ($this->accountUser->approved) {
                $package->approved = true;
            }

            try {
                $this->packageManager->validate($package, $this->accountUser->admin);
                $package = $this->packageManager->syncPackage($package);
                $this->em->persist($package);
                $this->em->flush();
                return new RedirectResponse($this->router->generate('submitted')); 
            } catch (\Exception $e) {
                $request->getSession()->getFlashBag()->add('error', "Package has an invalid composer.json!");
                $request->getSession()->getFlashBag()->add('warning', $e->getMessage());
                $package->approved = false; 
                $error = 'invalid';
            }
            
            
        }
        return new Response($this->renderer->render("submit.html", ['form'=>$form->createView(), 'error'=>$error]));

    }
    
    

}