<?php

namespace Bundle\Site\MarketPlace\Action;

use Bundle\Site\MarketPlace\Storage\Entity;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Disable package action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class PackageDisable extends AbstractAction
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
        $repo = $em->getRepository(Entity\Package::class);

        $package = $repo->findOneBy(['id' => $params['package'], 'account' => $request->get('user')]);
        if ($package === false) {
            $session->getFlashBag()->add('error', 'There was a problem accessing this package');

            return new RedirectResponse($route);
        }

        $package->setApproved(false);
        $repo->save($package);

        $session->getFlashBag()->add('info', 'This extension has been disabled.');

        return new RedirectResponse($route);
    }
}
