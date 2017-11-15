<?php

namespace Bundle\Site\MarketPlace\Action;

use Bundle\Site\MarketPlace\Storage\Entity;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class Admin extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        $repo = $em->getRepository(Entity\Package::class);
        $packages = $repo->findAll();

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $html = $twig->render('admin.twig', ['packages' => $packages]);

        return new Response($html);
    }
}
