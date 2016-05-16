<?php
namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Entity;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class Profile
{
    public function __construct(Twig_Environment $renderer, EntityManager $em)
    {
        $this->renderer = $renderer;
        $this->em = $em;
    }
    
    public function __invoke(Request $request)
    {
        $user = $request->attributes->get('user');
        $repo = $this->em->getRepository(Entity\Package::class);
        $packages = $repo->findBy(['account' => $user], ['created' => 'DESC']);

        return new Response($this->renderer->render('profile.twig', ['packages' => $packages, 'user' => $user]));
    }
}
