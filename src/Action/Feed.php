<?php

namespace Bundle\Site\MarketPlace\Action;

use Bundle\Site\MarketPlace\Storage\Entity;
use Bundle\Site\MarketPlace\Storage\Repository\Package;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Feed action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class Feed extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        /** @var Package $repo */
        $repo = $em->getRepository(Entity\Package::class);
        $packages = $repo->search(null, null, 'date');

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $html = $twig->render('feed.xml.twig', ['packages' => $packages]);

        $response = new Response($html);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
