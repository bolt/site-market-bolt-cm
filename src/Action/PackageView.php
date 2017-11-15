<?php

namespace Bundle\Site\MarketPlace\Action;

use Bundle\Site\MarketPlace\Service\BoltThemes;
use Bundle\Site\MarketPlace\Service\PackageManager;
use Bundle\Site\MarketPlace\Storage\Entity;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Package view action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class PackageView extends AbstractAction
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

        /** @var Entity\Package $package */
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
            $suggestedPackage = $repo->findOneBy(['name' => $name, 'approved' => true]);
            if ($suggestedPackage) {
                $suggested[] = [
                    'package'     => $suggestedPackage,
                    'description' => $description,
                ];
            }
        }
        $stopwatch->stop('marketplace.action.view.suggested');

        $webhook = $this->getWebhookData($package);
        if ($webhook && $webhook['latest'] === false) {
            /** @var Session $session */
            $session = $this->getAppService('session');
            $session->getFlashBag()->add('warning', 'No webhook notifications have been received for this package. Updates might take up to 24 hours to appear.');
        }

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        /** @var BoltThemes $themeManager */
        $themesManager = $this->getAppService('marketplace.manager_themes');
        /** @var PackageManager $packageManager */
        $packageManager = $this->getAppService('marketplace.manager_package');
        $context = [
            'package'    => $package,
            'related'    => $repo->findBy(['account_id' => $package->getAccountId(), 'approved' => true], null, 8),
            'readme'     => $packageManager->getReadme($package),
            'boltthemes' => $themesManager->info($package),
            'suggested'  => $suggested,
            'statistics' => $this->getAppService('marketplace.manager_statistics'),
            'webhook'    => $webhook,
            'updated'    => $this->getUpdated($package),
            'versions'   => $this->getVersions($package),
        ];
        $html = $twig->render('package.twig', $context);

        $stopwatch->stop('marketplace.action.view');

        return new Response($html);
    }
}
