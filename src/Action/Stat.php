<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Statistics action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
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
        /** @var Repository\Package $packageRepo */
        $packageRepo = $em->getRepository(Entity\Package::class);
        /** @var Entity\Package $package */
        $package = $packageRepo->findOneBy(['name' => $package]);

        /** @var Repository\StatInstall $statRepo */
        $statRepo = $em->getRepository(Entity\StatInstall::class);
        $stat = new Entity\StatInstall([
            'source'     => $request->server->get('HTTP_REFERER'),
            'ip'         => $request->server->get('REMOTE_ADDR'),
            'recorded'   => new \DateTime(),
            'package_id' => $package,
            'version'    => $version,
            'type'       => $type,
        ]);
        $statRepo->save($stat);

        if ($type === 'star') {
            $route = $urlGen->generate('view', ['package' => $package->getId()]);

            return new RedirectResponse($route);
        }
        $response = new JsonResponse(['status' => 'OK', 'package' => $package->getId()]);
        $response->setCallback($request->get('callback'));

        return $response;
    }
}
