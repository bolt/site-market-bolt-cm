<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Bolt\Extensions\Entity;


class Admin extends AbstractAction
{
    
    public function __invoke(Request $request)
    {
        $repo = $this->em->getRepository(Entity\Package::class);
        $packages = $repo->findAll();
        return new Response($this->renderer->render("admin.html", ['packages'=>$packages]));

    }
}