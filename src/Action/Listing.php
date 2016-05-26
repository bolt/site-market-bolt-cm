<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository\Package;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Listing action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class Listing extends AbstractAction
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
        $latest = $repo->findBy(['approved' => true], ['created' => 'DESC'], 10);
        $starred = $repo->getMostStarredStats(5);
        $downloaded = $repo->getMostDownloadedStats(6);
        $latest_themes = $repo->findBy(['approved' => true, 'type' => 'bolt-theme'], ['created' => 'DESC'], 3);

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $context = [
            'latest'        => $latest,
            'starred'       => $starred,
            'downloaded'    => $downloaded,
            'latest_themes' => $latest_themes,
            'popular'       => $repo->getPopularTags(),
        ];
        $html = $twig->render('index.twig', $context);

        return new Response($html);
    }
}
