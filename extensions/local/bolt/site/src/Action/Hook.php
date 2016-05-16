<?php
namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Entity;
use Bolt\Extension\Bolt\MarketPlace\Service\PackageManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class Hook
{
    public $em;
    public $packageManager;

    public function __construct(EntityManager $em, PackageManager $packageManager)
    {
        $this->em = $em;
        $this->packageManager = $packageManager;
    }

    public function __invoke(Request $request, $params)
    {
        $token = $request->get('token');
        $repo = $this->em->getRepository(Entity\Package::class);

        $package = $repo->findOneBy(['token' => $token]);

        if ($package) {
            $package = $this->packageManager->syncPackage($package);
            $response = ['status' => 'ok', 'response' => $package];
        } else {
            $response = ['status' => 'error', 'response' => 'package not found'];
        }

        return new JsonResponse($response);
    }
}
