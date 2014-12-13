<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManager;
use Bolt\Extensions\Entity;

class TestBuildCheck
{
    public $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    public function __invoke(Request $request, $params)
    {
        $repo = $this->em->getRepository(Entity\VersionBuild::class);
        $build = $repo->findOneBy(['id'=> $params['build']]);
        $response = ['status'=>$build->getStatus(), 'url'=>$build->getUrl(), 'testStatus'=>$build->getTestStatus()];
        return new JsonResponse($response);
    }
    

}