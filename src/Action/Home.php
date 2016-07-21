<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Home page action.
 *
 * @author Ross Riley <riley.ross@gmail.com>
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Home extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        /** @var Repository\Package $repo */
        $repo = $em->getRepository(Entity\Package::class);
        /** @var Repository\PackageStar $starRepo */
        $starRepo = $em->getRepository(Entity\PackageStar::class);

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $context = [
            'latest'                  => $repo->getLatest(10),
            'starred'                 => $starRepo->getRankedPackages(5),
            'downloaded'              => $repo->getMostDownloadedStats(6),
            'latest_themes'           => $repo->getLatest(3, 'bolt-theme'),
            'latest_plugins'          => $repo->getLatest(12, 'bolt-extension'),
            'most_downloaded_themes'  => $repo->getMostDownloaded('bolt-theme', 6),
            'most_downloaded_plugins' => $repo->getMostDownloaded('bolt-extension', 6),
            'popular'                 => $repo->getPopularTags(),
        ];
        $html = $twig->render('index.twig', $context);

        return new Response($html);
    }
}
