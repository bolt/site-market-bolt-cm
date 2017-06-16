<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository\Package;
use Bolt\Extension\BoltAuth\Auth\AccessControl\Authorisation;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Account profile starred packages action.
 *
 * @author Ross Riley <riley.ross@gmail.com>
 */
class AccountStarred extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        $token = $request->get('token');
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        /** @var Package $repo */
        $repo = $em->getRepository(Entity\Package::class);

        /** @var Authorisation $user */
        $user = $params['user'];
        $packages = $repo->getStarredPackages($user->getGuid());

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $html = $twig->render('account-starred.twig', ['packages' => $packages, 'user' => $user]);

        return new Response($html);
    }
}
