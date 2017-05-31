<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
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
        /** @var Repository\Package $repo */
        $repo = $em->getRepository(Entity\Package::class);
        /** @var Repository\PackageStar $starRepo */
        $starRepo = $em->getRepository(Entity\PackageStar::class);
        /** @var Repository\StatInstall $installRepo */
        $installRepo = $em->getRepository(Entity\StatInstall::class);

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $context = [
            'latest'        => $repo->findBy(['approved' => true], ['created' => 'DESC'], 10),
            'starred'       => $starRepo->getRankedPackages(5),
            'downloaded'    => $installRepo->getRankedPackages(6),
            'latest_themes' => $repo->findBy(['approved' => true, 'type' => 'bolt-theme'], ['created' => 'DESC'], 3),
            'popular'       => $repo->getPopularTags(),
        ];
        $html = $twig->render('index.twig', $context);

        return new Response($html);
    }
}
