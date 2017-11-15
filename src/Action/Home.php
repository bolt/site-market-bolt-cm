<?php

namespace Bundle\Site\MarketPlace\Action;

use Bundle\Site\MarketPlace\Storage\Entity;
use Bundle\Site\MarketPlace\Storage\Repository;
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
        /** @var Repository\StatInstall $installRepo */
        $installRepo = $em->getRepository(Entity\StatInstall::class);

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $context = [
            'latest'                  => $repo->getLatest(10),
            'starred'                 => $starRepo->getRankedPackages(5),
            'downloaded'              => $installRepo->getRankedPackages(6),
            'latest_themes'           => $repo->getLatest(3, 'bolt-theme'),
            'latest_plugins'          => $repo->getLatest(12, 'bolt-extension'),
            'most_downloaded_themes'  => $installRepo->getRankedPackages(6, 'bolt-theme'),
            'most_downloaded_plugins' => $installRepo->getRankedPackages(6, 'bolt-extension'),
            'popular'                 => $repo->getPopularTags(),
        ];
        $html = $twig->render('index.twig', $context);

        return new Response($html);
    }
}
