<?php
namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Entity;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ListPackages
{
    public $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    public function __invoke(Request $request, $params)
    {
        $repo = $this->em->getRepository(Entity\Package::class);
        
        if (isset($params['sort'])) {
            if ($params['sort'] == 'downloaded') {
                $packages = $repo->mostDownloaded(200);
            }
        } elseif ($search = $request->get('name')) {
            $packages = $repo->search($search);
        } else {
            $packages = $repo->findBy(['approved' => true]);
        }
        array_walk($packages, function (&$v, $k) {
            $v = $v->serialize();
            unset($v['approved']);
            unset($v['account']);
        });

        $response = new JsonResponse(['packages' => $packages]);
        $response->setCallback($request->get('callback'));

        return $response;
    }
}
