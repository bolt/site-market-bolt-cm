<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository\Package;
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
        /** @var Package $repo */
        $repo = $em->getRepository(Entity\Package::class);

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $context = [
            'latest'                  => $repo->getLatest(10),
            'starred'                 => $repo->mostStarred(5),
            'downloaded'              => $repo->mostDownloaded(6),
            'latest_themes'           => $repo->getLatest(3, 'bolt-theme'),
            'latest_plugins'          => $repo->getLatest(12, 'bolt-extension'),
            'most_downloaded_themes'  => $repo->search(null, 'bolt-theme', 'downloads'),
            'most_downloaded_plugins' => $repo->search(null, 'bolt-extension', 'downloads'),
            'popular'                 => $repo->popularTags(),
        ];
        $html = $twig->render('index.twig', $context);

        return new Response($html);
    }
}
