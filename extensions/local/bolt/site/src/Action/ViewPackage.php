<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Entity;
use Bolt\Extension\Bolt\MarketPlace\Service\PackageManager;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ViewPackage extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        /** @var UrlGeneratorInterface $urlGen */
        $urlGen = $this->getAppService('url_generator');
        /** @var Session $session */
        $session = $this->getAppService('session');
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        $repo = $em->getRepository(Entity\Package::class);

        $package = $repo->findOneBy(['id' => $params['package']]);

        if (!$package) {
            $session->getFlashBag()->add('error', 'There was a problem accessing this package');
            $route = $urlGen->generate('profile');

            return new RedirectResponse($route);
        }

        $suggested = [];
        foreach ($package->suggested as $name => $description) {
            $suggestedPackage = $repo->findOneBy(['name' => $name]);
            if ($suggestedPackage) {
                $suggested[] = [
                    'package'     => $suggestedPackage,
                    'description' => $description,
                ];
            }
        }

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $services = $this->getAppService('marketplace.services');
        /** @var PackageManager $packageManager */
        $packageManager = $services['package_manager'];
        $context = [
            'package'    => $package,
            'readme'     => $packageManager->getReadme($package),
            'allowedit'  => $package->account === $request->get('user'),
            'boltthemes' => $services['bolt_themes']->info($package),
            'suggested'  => $suggested,
        ];
        $html = $twig->render('view.twig', $context);

        return new Response($html);
    }
}
