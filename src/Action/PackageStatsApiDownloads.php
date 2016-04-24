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


class PackageStatsApiDownloads
{

    public $renderer;
    public $em;
    public $router;
    protected $colors = [];

    public function __construct(EntityManager $em, Router $router)
    {
        $this->em = $em;
        $this->router = $router;
        $this->colors = ([
	    	'206, 148, 140',
	    	'230, 181, 166',
	    	'247, 198, 165',
	    	'241, 201, 150',
	    	'253, 231, 174',
	    	'241, 215, 154',
	    	'207, 203, 174',
	    	'200, 212, 188',
	    	'224, 227, 224',
	    	'198, 203, 199',
	    	'190, 205, 224',
	    	'183, 196, 204',
	    	'226, 230, 232',
	    	'131, 140, 155',
	    	'214, 198, 173',
	    	'231, 214, 198',
	    	'223, 208, 169',
	    	//'253, 243, 211',
	    	'232, 232, 230',
	    	//'247, 247, 246',
	    ]); // http://www.milkpaint.com/color.html

	    shuffle($this->colors);
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

        $group = $request->get('group');
        $from = $request->get('from');
        $to = $request->get('to');
        $version = $request->get('version');

        $fromDT = null;
        $toDT = null;

        if($from != null && $to != null) {
            if($group === "months") {
                $fromDT= (new \DateTime($from))->modify('first day of this month');
                $toDT = (new \DateTime($to))->modify('first day of next month');
            }elseif ($group === "days"){
                $fromDT = (new \DateTime($from));
                $toDT = (new \DateTime($to))->modify('next day');
            }
        }

        $stats = $this->getStats($package, $version, $fromDT, $toDT);

        $allVersions = $this->getAllVersions($package);

		if($group === "months") {
			$data = $this->getDataGroupedByMonths($stats, $from, $to);
		}elseif ($group === "days"){
			$data = $this->getDataGroupedByDays($stats, $from, $to);
		}else{
            $data = $this->getDataGroupedByMonths($stats, $from, $to);
        }


        return new JsonResponse(array_merge(
        	$data,
        	[
        		'allVersions' => $allVersions
        	]
        ));
    }

    private function getStats($package, $version, $from = null, $to = null)
    {
        $repo = $this->em->getRepository(Entity\Stat::class);

        $qb = $repo->createQueryBuilder('s')
              ->where('s.type = :type')
              ->andWhere('s.package = :package')
              ->setParameter('type', 'install')
              ->setParameter('package', $package->id);

        if($from != null && $to != null) {
            $from =$from->format("Y-m-d H:i:s");
            $to =$to->format("Y-m-d H:i:s");

            $qb = $qb
                ->andWhere('s.recorded >= :from')
                ->andWhere('s.recorded < :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }

        if($version != null && $version != ''){
            $qb = $qb
                ->andWhere('s.version = :version')
                ->setParameter('version', $version);
        }

        $q = $qb->getQuery();

        $stats = $q->execute();

        return $stats;
    }

    private function getDataGroupedByMonths($stats, $from, $to)
    {
    	$months = [];

        if($from != null && $to != null) {
        	$months = $this->getMonthsFromRange($from, $to);
        }else{
        	// getting all months with downloads
			foreach ($stats as $stat) {
            	$months[$stat->recorded->format('Y-m')]['date'] = $stat->recorded;
	        }

	        ksort($months);
        }

        // get all the different downloaded package versions
        $versions = $this->getVersions($stats);

        $labels = [];
        $values = [];

        // build the labels for the months
        foreach($months as $month) {
        	$labels[] = $month['date']->format('F Y');
        }

        $colorIndex = 0;
        // get download counts for each months for each version
        foreach ($versions as $version) {
        	$item['label'] = $version;

        	if(!isset($this->colors[$colorIndex])){
        		$colorIndex = 0;
        	}

        	$item = $this->applyColors($item, $colorIndex);

        	$colorIndex++;

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
        	'datasets' => $values
        ];
    }

    private function getDataGroupedByDays($stats, $from, $to)
    {
        $days = [];

        if($from != null && $to != null) {
            $days = $this->getDaysFromRange($from, $to);
        }else{
            // getting all months with downloads
            foreach ($stats as $stat) {
                $days[$stat->recorded->format('Y-m-d')]['date'] = $stat->recorded;
            }

            ksort($days);
        }

        // get all the different downloaded package versions
        $versions = $this->getVersions($stats);

        $labels = [];
        $values = [];

        // build the labels for the months
        foreach($days as $day) {
            $labels[] = $day['date']->format('d F Y');
        }

        $colorIndex = 0;
        // get download counts for each months for each version
        foreach ($versions as $version) {
            $item['label'] = $version;

            if(!isset($this->colors[$colorIndex])){
                $colorIndex = 0;
            }

            $item = $this->applyColors($item, $colorIndex);

            $colorIndex++;

            $item['data'] = [];

            foreach($days as $day => $value) {
                $item['data'][] = count($this->getInstallsByVersionAndDate($stats, $version, $day, 'Y-m-d'));
            }
            $values[] = $item;
        }

        return [
            //'versions' => $versions,
            //'months' => $months,
            'labels' => $labels,
            'datasets' => $values
        ];
    }

    private function getVersions($stats)
    {
    	$versions = [];
        foreach($stats as $stat) {
        	if($stat->type == 'install' && $stat->version != null && $stat->version != ''){
        		$versions[$stat->version] = 1;
        	}
        }
        ksort($versions);
        $versions = array_keys($versions);

        return $versions;
    }

    private function getAllVersions($package)
    {
        $repo = $this->em->getRepository(Entity\Stat::class);

        $qb = $repo->createQueryBuilder('s')
              ->where('s.type = :type')
              ->andWhere('s.package = :package')
              ->setParameter('type', 'install')
              ->setParameter('package', $package->id)
              ->groupBy('s.version');

        $q = $qb->getQuery();

        $results = $q->execute();

        $versions = [];

        foreach($results as $result){
            $versions[] = $result->version;
        }

        return $versions;
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

    private function getMonthsFromRange($from, $to)
    {
    	$months = [];
    	$start    = (new \DateTime($from))->modify('first day of this month');
		$end      = (new \DateTime($to))->modify('first day of next month');
		$interval = \DateInterval::createFromDateString('1 month');
		$period   = new \DatePeriod($start, $interval, $end);

		foreach ($period as $dt) {
		    $months[$dt->format('Y-m')]['date'] = $dt;
		}

		return $months;
    }

    private function getDaysFromRange($from, $to)
    {
        $days = [];
        $start    = (new \DateTime($from));
        $end      = (new \DateTime($to))->modify('next day');
        $interval = \DateInterval::createFromDateString('1 day');
        $period   = new \DatePeriod($start, $interval, $end);

        foreach ($period as $dt) {
            $days[$dt->format('Y-m-d')]['date'] = $dt;
        }

        return $days;
    }

    private function applyColors($item, $colorIndex)
    {
        $item['fillColor'] = 'rgba(' . $this->colors[$colorIndex] . ', 0.2)';
        $item['strokeColor'] = 'rgba(' . $this->colors[$colorIndex] . ', 1)';
        $item['pointColor'] = 'rgba(' . $this->colors[$colorIndex] . ', 1)';
        $item['pointStrokeColor'] = "#fff";
        $item['pointHighlightFill'] = 'rgba(' . $this->colors[$colorIndex] . ', 1)';
        $item['pointHighlightStroke'] = 'rgba(' . $this->colors[$colorIndex] . ', 1)';

        return $item;
    }

}
