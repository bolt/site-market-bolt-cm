<?php

namespace Bundle\Site\MarketPlace\Action;

use Bundle\Site\MarketPlace\Storage\Entity;
use Bundle\Site\MarketPlace\Storage\Repository\Package;
use Bolt\Extension\BoltAuth\Auth\AccessControl\Authorisation;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Account profile action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class AccountProfile extends AbstractAction
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
        $packages = $repo->findBy(['account_id' => $user->getGuid()], ['title', 'ASC']);

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $html = $twig->render('account-profile.twig', ['packages' => $packages, 'user' => $user]);

        return new Response($html);
    }
}
