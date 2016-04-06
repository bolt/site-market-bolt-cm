<?php
namespace Bolt\Extensions\Action;

use Aura\Router\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Twig_Environment;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactory;
use Bolt\Extensions\Entity;


class PackageStatsApi
{

    public $renderer;
    public $em;
    public $router;

    public function __construct(EntityManager $em, Router $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    public function __invoke(Request $request, $params)
    {
        $repo = $this->em->getRepository(Entity\Package::class);
        //$package = $repo->findOneBy(['id'=>$params['package'], 'account'=>$request->get('user')]);
        $package = $repo->findOneBy(['id'=>$params['package']]);

        if(!$package) {
            return new JsonResponse([
            	'error' => [
            		'message' => 'No package found or you don\'t own it'
            	]
            ]);
        }
		$stats = $package->stats;

		$data = $this->getAllTimeMonths($stats);



        //foreach($months as $month) {
        //	$labels[] = $month['date']->format('F Y');
        //	foreach($month['stats'] as $version => $stats) {
        //		$values[$month['date']->format('Y-m')][$version] = count($stats);
        //	}
        //}


        //foreach($downloads as $ver=>$hits) {
            //$downloads[$ver] = count($hits);
        //}
        // $stats[0]->recorded->format('F')
        return new JsonResponse($data);
    }

    private function getAllTimeMonths($stats)
    {
    	// getting all months with downloads
		$months = [];
		foreach ($stats as $stat) {
            if($stat->type == 'install') {
            	$months[$stat->recorded->format('Y-m')]['date'] = $stat->recorded;
            	//$months[$stat->recorded->format('Y-m')]['stats'][$stat->version][] = $stat;
                //$downloads[$stat->version][] = $stat->ip;
            }
        }

        ksort($months);

        // get all the different downloaded package versions
        $versions = [];
        foreach($stats as $stat) {
        	if($stat->version != null && $stat->version != ''){
        		$versions[$stat->version] = 1;
        	}
        }
        ksort($versions);
        $versions = array_keys($versions);

        $labels = [];
        $values = [];

        // build the labels for the months
        foreach($months as $month) {
        	$labels[] = $month['date']->format('F Y');
        }

        // get download counts for each months for each version
        foreach ($versions as $version) {
        	$item['label'] = $version;
        	$item['data'] = [];
        	foreach($months as $month => $value) {
        		$item['data'][] = count($this->getInstallsByVersionAndDate($stats, $version, $month, 'Y-m'));
        	}
        	$values[] = $item;
        }

        return [
        	//'versions' => $versions,
        	//'months' => $months,
        	'labels' => $labels,
        	'values' => $values
        ];
    }

    private function getInstallsByVersionAndDate($stats, $version, $date, $dateFormat)
    {
    	$installs = [];
    	foreach ($stats as $stat) {
            if($stat->type == 'install' && $stat->version == $version && $stat->recorded->format($dateFormat) == $date) {
            	$installs[] = $stat;
            }
        }

        return $installs;
    }
}
