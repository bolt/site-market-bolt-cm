<?php

namespace Bundle\Site\MarketPlace\Action;

use Bundle\Site\MarketPlace\Storage\Entity;
use Bundle\Site\MarketPlace\Storage\Repository\Package;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bolt v3 ready check action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class V3Ready extends AbstractAction
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

        $packages = $repo->findBy([], ['title' => 'ASC']);

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $html = $twig->render('v3ready.twig', ['packages' => $packages]);

        return new Response($html);
    }
}
