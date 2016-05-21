<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PackagesByAuthor extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        /** @var UrlGeneratorInterface $urlGen */
        $urlGen = $this->getAppService('url_generator');
        $route = $urlGen->generate('homepage');
        /** @var Session $session */
        $session = $this->getAppService('session');
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        /** @var Repository\Package $repo */
        $repo = $em->getRepository(Entity\Package::class);

        $packages = $repo->getAllByComposerAuthor($params['package_author']);
        if ($packages === false) {
            $session->getFlashBag()->add('error', 'There was a problem finding this author ' . $params['package_author']);

            return new RedirectResponse($route);
        }

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $context = ['packages' => $packages];
        $html = $twig->render('package-list.twig', $context);

        return new Response($html);
    }
}
