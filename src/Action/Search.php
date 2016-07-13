<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository\Package;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Search action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class Search extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        $search = $request->get('q');
        $type = $request->get('type') ?: null;
        $order = $request->get('order') ?: null;

        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        /** @var Package $repo */
        $repo = $em->getRepository(Entity\Package::class);
        if ($params['version'] === null) {
            $packages = $repo->search($search, $type, $order);
        } else {
            $packages = $repo->searchByVersion($search, $type, $params['version'], $order);
        }

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $context = [
            'results' => $packages,
            'term'    => $search,
            'layout'  => $params['type'] === 'browse' ? 'layout.twig' : 'ajax.twig',
        ];
        $html = $twig->render('search.twig', $context);

        return new Response($html);
    }
}
