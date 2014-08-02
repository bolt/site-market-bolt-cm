<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Bolt\Extensions\Entity;


class Profile extends AbstractAction
{
    
    public function __invoke(Request $request)
    {
        if (! $this->restrictAccess($request)) {
            return new RedirectResponse($this->router->generate('login'));
        }
        $repo = $this->em->getRepository(Entity\Package::class);
        $packages = $repo->findBy(['account'=>$this->accountUser], ['created'=>'DESC']);
        return new Response($this->renderer->render("profile.html", ['packages'=>$packages, 'user'=>$this->accountUser]));

    }
}