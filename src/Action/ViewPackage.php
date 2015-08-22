<?php
namespace Bolt\Extensions\Action;

use Aura\Router\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Composer\Util\ConfigValidator;
use Composer\IO\NullIO;
use Twig_Environment;
use Doctrine\ORM\EntityManager;
use Bolt\Extensions\Entity;
use Bolt\Extensions\Service\PackageManager;



class ViewPackage
{
    
    public $renderer;
    public $em;
    public $packageManager;
    public $router;
    
    public function __construct(Twig_Environment $renderer, EntityManager $em, PackageManager $packageManager, Router $router)
    {
        $this->renderer = $renderer;
        $this->em = $em;
        $this->packageManager = $packageManager;
        $this->router = $router;
    }
    
    public function __invoke(Request $request, $params)
    {
        $repo = $this->em->getRepository(Entity\Package::class);
        $package = $repo->findOneBy(['id'=>$params['package']]);
        
        
        if(!$package) {
            $request->getSession()->getFlashBag()->add('error', "There was a problem accessing this package");
            return new RedirectResponse($this->router->generate('profile'));
        }
        
        $versions = ['dev'=>[],'stable'=>[]];               
        $allowedit = false;
        $readme = null;

        try {
            $allowedit = $package->account === $request->get('user');
            $repo = $this->em->getRepository(Entity\VersionBuild::class);
            $info = $this->packageManager->getInfo($package, false);
            $readme = $this->packageManager->getReadme($package);
            foreach($info as $ver) {
                $build = $repo->findOneBy(['package'=>$package->id, 'version'=>$ver['version']]);
                if($build) {
                    $ver['build'] = $build;
                } 
                $versions[$ver['stability']][] = $ver;
            }
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('error', $e->getMessage());
        }
                       
        return new Response(
            $this->renderer->render(
                "view.html", 
                [
                    'package' => $package, 
                    'versions' => $versions,
                    'readme' => $readme, 
                    'allowedit' => $allowedit
                ]
            )
        );

    }
}