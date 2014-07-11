<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Bolt\Extensions\Entity;

use Composer\IO\NullIO;
use Composer\Factory;
use Composer\Repository\VcsRepository;


class Submit extends AbstractAction
{
    
    public function __invoke(Request $request)
    {
        putenv("COMPOSER_HOME=".sys_get_temp_dir());
        $error = false;

        $entity = new Entity\Package;
        $form = $this->forms->create('package', $entity);
        $form->handleRequest();

        if ($form->isValid()) {
            $package = $form->getData();
            $package->created = new \DateTime;
            try {
                $information = $this->readComposer($package);
                $package->name = $information['name'];
                $package->keywords = $information['keywords'];
                $this->em->persist($package);
                $this->em->flush();
                return new RedirectResponse($this->router->generate('submitted'));
            } catch (\Exception $e) {
                $error = 'invalid';
            }
            
            
        }
        return new Response($this->renderer->render("submit.html", ['form'=>$form->createView(), 'error'=>$error]));

    }
    
    
    protected function readComposer($package)
    {
        $io = new NullIO();
        $config = Factory::createConfig();
        $io->loadConfiguration($config);
            
        $repository = new VcsRepository(['url' => $package->getSource()], $io, $config);
        $driver = $repository->getDriver();
        $information = $driver->getComposerInformation($driver->getRootIdentifier());
        return $information;
    }
}