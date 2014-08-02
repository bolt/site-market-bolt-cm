<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

use Bolt\Extensions\Entity;


class Search extends AbstractAction
{
    
    public function __invoke(Request $request, $params)
    {
        $search = $request->get('q');
        $repo = $this->em->getRepository(Entity\Package::class);
        $packages = $repo->createQueryBuilder('p')
                ->where('p.approved = :status')
                ->andWhere('p.name LIKE :search')
                ->orWhere('p.title LIKE :search')
                ->orWhere('p.keywords LIKE :search')
                ->setParameter('status', true)
                ->setParameter('search', "%".$search."%")
                ->getQuery()
                ->getResult();

        return new Response($this->renderer->render("search.html", ['results'=>$packages, 'term'=>$search]));

        
    }
}