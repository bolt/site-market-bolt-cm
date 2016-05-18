<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Entity;
use Bolt\Extension\Bolt\MarketPlace\Repository\Package;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $html = $twig->render('feed.xml', ['packages' => $packages]);

        $response = new Response($html);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
