<?php

namespace Bundle\Site\MarketPlace\Twig;

use Twig_Extension as TwigExtension;
use Twig_SimpleFilter as TwigSimpleFilter;
use Twig_SimpleFunction as TwigSimpleFunction;

/**
 * Twig extension class.
 *
 * @author Ross Riley <riley.ross@gmail.com>
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class MarketCoreExtension extends TwigExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $safe = ['is_safe' => ['html']];

        return [
            new  TwigSimpleFunction('buildStatus', [MarketCoreRuntime::class, 'buildStatus'], $safe),
            new  TwigSimpleFunction('gravatar',    [MarketCoreRuntime::class, 'gravatar'],    $safe),
            new  TwigSimpleFunction('package',     [MarketCoreRuntime::class, 'getPackage'],  $safe),
            new  TwigSimpleFunction('packageIcon', [MarketCoreRuntime::class, 'packageIcon'], $safe),
            new  TwigSimpleFunction('getenv',      [MarketCoreRuntime::class, 'getenv'],      $safe),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigSimpleFilter('humanTime', [MarketCoreRuntime::class, 'humanTime']),
        ];
    }
}
