<?php

namespace Bolt\Extension\Bolt\MarketPlace\Twig;

use Bolt\Extension\Bolt\MarketPlace\Service\RecordManager;
use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use forxer\Gravatar\Gravatar;
use Pimple as Container;
use Twig_Extension as TwigExtension;
use Twig_SimpleFilter as TwigSimpleFilter;
use Twig_SimpleFunction as TwigSimpleFunction;

/**
 * Twig extension class.
 *
 * @author Ross Riley <riley.ross@gmail.com>
 */
class Extension extends TwigExtension
{
    public $statusTemplate = '<div class="buildstatus ui icon label %s" data-content="%s"><i class="icon %s"></i> %s <span class="version">%s</span></div>';

    /** @var Container */
    protected $services;

    /**
     * Constructor.
     *
     * @param Container $services
     */
    public function __construct(Container $services)
    {
        $this->services = $services;
    }

    public function getName()
    {
        return 'bolt_helper';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $safe = ['is_safe' => ['html']];

        return [
            new  TwigSimpleFunction('buildStatus', [$this, 'buildStatus'], $safe),
            new  TwigSimpleFunction('gravatar',    [$this, 'gravatar'],    $safe),
            new  TwigSimpleFunction('package',     [$this, 'getPackage'],  $safe),
            new  TwigSimpleFunction('packageIcon', [$this, 'packageIcon'], $safe),
            new  TwigSimpleFunction('getenv',      [$this, 'getenv'],      $safe),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigSimpleFilter('humanTime', [$this, 'humanTime']),
        ];
    }

    public function buildStatus($build, $options = [])
    {
        if (!$build || $build->testStatus === 'pending') {
            return sprintf($this->statusTemplate, 'orange', 'This version is currently awaiting a test result', 'wait', 'not setup', '');
        }

        if ($build->phpTarget) {
            $php = str_replace('php', '', $build->phpTarget);
            $php = substr_replace($php, '.', 1, 0);
            $php .= '+';
        } else {
            $php = '5.6';
        }

        if ($build->testStatus === 'approved') {
            return sprintf($this->statusTemplate, 'green', 'This version is an approved build', 'checkmark', $build->testStatus, 'for PHP ' . $php);
        }

        if ($build->testStatus === 'failed') {
            return sprintf($this->statusTemplate, 'red', 'This version is not an approved build', 'remove', $build->testStatus, 'for PHP ' . $php);
        }
    }

    public function gravatar($email, $options = [])
    {
        return Gravatar::image($email);
    }

    public function humanTime($time, $suffix = '')
    {
        if ($time instanceof \DateTime) {
            $time = $time->getTimestamp();
        }

        if (!$time) {
            return 'never';
        }

        $time = time() - $time; // to get the time since that moment

        $tokens = [
            31536000 => 'year',
            2592000  => 'month',
            604800   => 'week',
            86400    => 'day',
            3600     => 'hour',
            60       => 'minute',
            1        => 'second',
        ];

        foreach ($tokens as $unit => $text) {
            if ($time < $unit) {
                continue;
            }
            $numberOfUnits = floor($time / $unit);

            return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '') . $suffix;
        }
    }

    /**
     * @param Entity\Package $package
     *
     * @return string
     */
    public function packageIcon(Entity\Package $package)
    {
        if ($ico = $package->getIcon()) {
            if (strpos('//', $ico)) {
                return $package->getIcon();
            } else {
                $ico = str_replace('github.com', 'raw.githubusercontent.com', $package->getSource());
                $ico = $ico . '/master/' . $package->getIcon();

                return $ico;
            }
        }

        return '/files/' . $package->getType() . '.png';
    }

    /**
     * @param string $packageId
     *
     * @return Entity\Package|false
     */
    public function getPackage($packageId)
    {
        /** @var RecordManager $recordManager */
        $recordManager = $this->services['record_manager'];
        
        return $recordManager->getPackageById($packageId);
    }

    public function getenv($key)
    {
        return getenv($key);
    }
}
