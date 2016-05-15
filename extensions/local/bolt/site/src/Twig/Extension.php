<?php

namespace Bolt\Extension\Bolt\ExtensionSite\Twig;

use forxer\Gravatar\Gravatar;
use Twig_Extension as TwigExtension;
use Twig_Markup as TwigMarkup;
use Twig_SimpleFilter as TwigSimpleFilter;
use Twig_SimpleFunction as TwigSimpleFunction;

class Extension extends TwigExtension
{
    public $statusTemplate = '<div class="buildstatus ui icon label %s" data-content="%s"><i class="icon %s"></i> %s <span class="version">%s</span></div>';

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

    public function packageIcon($package)
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

        return '/images/' . $package->getType() . '.png';
    }

    public function getenv($key)
    {
        return getenv($key);
    }
}
