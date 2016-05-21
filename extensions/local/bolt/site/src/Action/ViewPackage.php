<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Service\PackageManager;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class ViewPackage extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        /** @var Stopwatch $stopwatch */
        $stopwatch = $this->getAppService('stopwatch');
        $stopwatch->start('marketplace.action.view');

        $stopwatch->start('marketplace.action.view.package');
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        $repo = $em->getRepository(Entity\Package::class);

        if (isset($params['package'])) {
            $package = $repo->findOneBy(['id' => $params['package']]);
        } else {
            $package = $repo->findOneBy(['name' => $params['package_author'] . '/' . $params['package_name']]);
        }

        $stopwatch->stop('marketplace.action.view.package');

        if (!$package) {
            /** @var Session $session */
            $session = $this->getAppService('session');
            $session->getFlashBag()->add('error', 'There was a problem accessing this package');

            /** @var UrlGeneratorInterface $urlGen */
            $urlGen = $this->getAppService('url_generator');
            $route = $urlGen->generate('home');

            $stopwatch->stop('marketplace.action.view');

            return new RedirectResponse($route);
        }

        $stopwatch->start('marketplace.action.view.suggested');
        $suggested = [];
        foreach ($package->getSuggested() as $name => $description) {
            $suggestedPackage = $repo->findOneBy(['name' => $name]);
            if ($suggestedPackage) {
                $suggested[] = [
                    'package'     => $suggestedPackage,
                    'description' => $description,
                ];
            }
        }
        $stopwatch->stop('marketplace.action.view.suggested');

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $services = $this->getAppService('marketplace.services');
        /** @var PackageManager $packageManager */
        $packageManager = $services['package_manager'];
        $context = [
            'package'    => $package,
            'related'    => $repo->findBy(['account_id' => $package->getAccountId()], null, 8),
            'readme'     => $packageManager->getReadme($package),
            'allowedit'  => $package->account === $request->get('user'),
            'boltthemes' => $services['bolt_themes']->info($package),
            'suggested'  => $suggested,
        ];
        $html = $twig->render('view.twig', $context);

        $stopwatch->stop('marketplace.action.view');

        return new Response($html);
    }
}
