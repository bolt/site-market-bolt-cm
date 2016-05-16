<?php
namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Entity;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

class JsonSearch
{
    public $em;
    public $renderer;

    public function __construct(Twig_Environment $renderer, EntityManager $em)
    {
        $this->em = $em;
        $this->renderer = $renderer;
    }

    public function __invoke(Request $request, $params)
    {
        $search = $request->get('q');
        $type = $request->get('type') ?: null;
        $order = $request->get('order') ?: null;
        $repo = $this->em->getRepository(Entity\Package::class);
        $packages = $repo->search($search, $type, $order);

        $result = [];
        foreach ($packages as $package) {
            $result[] = $this->formatPackage($package);
        }

        return new JsonResponse($result);
    }

    private function formatPackage($package)
    {
        return [
            'id'          => $package->id,
            'title'       => $package->title,
            'source'      => $package->source,
            'name'        => $package->name,
            'keywords'    => $package->keywords,
            'type'        => $package->type,
            'description' => $package->description,
            //'documentation' => $package->documentation,
            'approved'     => $package->approved,
            'requirements' => $package->requirements,
            'versions'     => $package->versions,
            'created'      => $package->created,
            'updated'      => $package->updated,
            'authors'      => $package->authors,
            'user'         => [
                'id'         => $package->account->id,
                'username'   => $package->account->username,
                'name'       => $package->account->name,
                'email_hash' => [
                    'type' => 'md5',
                    'hash' => md5($package->account->email),
                ],
            ],
            //'token' => $package->token,
            //'stats' => $package->stats,
            //'builds' => $package->builds,
            'screenshots' => $package->screenshots,
            'icon'        => $package->icon,
            'support'     => $package->support,
        ];
    }
}
