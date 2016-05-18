<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Entity;
use Bolt\Extension\Bolt\MarketPlace\Repository\Package;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $latest = $repo->findBy(['approved' => true], ['created' => 'DESC'], 10);
        $starred = $repo->mostStarred(5);
        $downloaded = $repo->mostDownloaded(6);
        $latest_themes = $repo->findBy(['approved' => true, 'type' => 'bolt-theme'], ['created' => 'DESC'], 3);
        $latest_plugins = $repo->findBy(['approved' => true, 'type' => 'bolt-extension'], ['created' => 'DESC'], 12);
        $mdownloaded_themes = $repo->search(null, 'bolt-theme', 'downloads');
        $mdownloaded_plugins = $repo->search(null, 'bolt-extension', 'downloads');

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $context = [
            'latest'              => $latest,
            'starred'             => $starred,
            'downloaded'          => $downloaded,
            'latest_themes'       => $latest_themes,
            'latest_plugins'      => $latest_plugins,
            'mdownloaded_themes'  => $mdownloaded_themes,
            'mdownloaded_plugins' => $mdownloaded_plugins,
            'popular'             => $repo->popularTags(),
        ];
        $html = $twig->render('index.twig', $context);

        return new Response($html);
    }
}
