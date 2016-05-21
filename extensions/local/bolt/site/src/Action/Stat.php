<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Stat extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        $type = $params['id'];
        $package = $params['package'];
        $version = isset($params['version']) ? $params['version'] : false;

        /** @var UrlGeneratorInterface $urlGen */
        $urlGen = $this->getAppService('url_generator');
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        $repo = $em->getRepository(Entity\Package::class);

        $package = $repo->findOneBy(['name' => $package]);

        $stat = new Entity\Stat([
            'source'   => $request->server->get('HTTP_REFERER'),
            'ip'       => $request->server->get('REMOTE_ADDR'),
            'recorded' => new \DateTime(),
            'package'  => $package,
            'version'  => $version,
            'type'     => $type,
        ]);

        $repo->save($stat);

        if ($type == 'star') {
            $route = $urlGen->generate('view', ['package' => $package->id]);

            return new RedirectResponse($route);
        }
        $response = new JsonResponse(['status' => 'OK', 'package' => $package->id]);
        $response->setCallback($request->get('callback'));

        return $response;
    }
}
