<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Service\PackageManager;
use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\BoltAuth\Auth\AccessControl\Authorisation;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Extension test listing action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class TestListing extends AbstractAction
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
        $packageRepo = $em->getRepository(Entity\Package::class);
        $versionBuildRepo = $em->getRepository(Entity\VersionBuild::class);
        /** @var PackageManager $packageManager */
        $packageManager = $this->getAppService('marketplace.manager_package');
        /** @var Authorisation $member */
        $member = $this->getAppService('auth.session')->getAuthorisation();
        /** @var Entity\Package $package */
        $package = $packageRepo->findOneBy(['id' => $params['package'], 'account_id' => $member->getGuid()]);
        if (!$package || $package->getAccountId() !== $member->getGuid()) {
            $session->getFlashBag()->add('error', 'There was a problem accessing this package');
            $route = $urlGen->generate('profile');

            return new RedirectResponse($route);
        }

        $versions = [
            'dev'    => null,
            'beta'   => null,
            'rc'     => null,
            'stable' => null,
        ];

        try {
            $info = $packageManager->getInfo($package, false);
        } catch (\Exception $e) {
            $info = null;
            $session->getFlashBag()->add('error', $e->getMessage());
        }

        foreach ($info as $ver) {
            $build = $versionBuildRepo->findOneBy(['package_id' => $package->getId(), 'version' => $ver['version']]);
            if ($build) {
                $ver['build'] = $build;
            }
            $versions[$ver['stability']][] = $ver;
        }

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $context = [
            'package'  => $package,
            'versions' => $versions,
        ];

        $html = $twig->render('extension-test-listing.twig', $context);

        return new Response($html);
    }
}
