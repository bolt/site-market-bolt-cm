<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Config;
use Bolt\Extension\Bolt\MarketPlace\Service\PackageManager;
use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
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
            $suggestedPackage = $repo->findOneBy(['name' => $name]);
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
        $services = $this->getAppService('marketplace.services');
        /** @var PackageManager $packageManager */
        $packageManager = $services['package_manager'];
        $context = [
            'package'    => $package,
            'related'    => $repo->findBy(['account_id' => $package->getAccountId()], null, 8),
            'readme'     => $packageManager->getReadme($package),
            'boltthemes' => $services['bolt_themes']->info($package),
            'suggested'  => $suggested,
            'statistics' => $this->getAppService('marketplace.services')['statistics'],
            'webhook'    => $webhook,
            'versions'   => $this->getVersions($package),
        ];
        $html = $twig->render('package.twig', $context);

        $stopwatch->stop('marketplace.action.view');

        return new Response($html);
    }

    protected function getVersions(Entity\Package $package)
    {
        $em = $this->getAppService('storage');
        /** @var Repository\PackageVersions $repo */
        $repo = $em->getRepository(Entity\PackageVersions::class);
        /** @var Config $config */
        $config = $this->getAppService('config');
        $boltMajorVersions = $config->get('general/bolt_major_versions');

        $versions = [];
        foreach ($boltMajorVersions as $boltMajorVersion) {
            $entity = $repo->getLatestCompatibleVersion($package->getId(), 'stable', $boltMajorVersion);
            if ($entity === false) {
                continue;
            }
            $versions[$boltMajorVersion] = $entity;
        }
        
        return $versions;
    }
}
