<?php

namespace Bundle\Site\MarketPlace\Service\Queue;

use Bolt\Configuration\PathResolver;
use Bolt\Storage\EntityManager;
use Composer\Composer;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Abstract queue.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
abstract class AbstractQueue
{
    /** @var EntityManager */
    protected $em;
    /** @var PathResolver */
    protected $pathResolver;
    /** @var Composer */
    protected $composer;
    /** @var array */
    protected $config;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param PathResolver  $pathResolver
     */
    public function __construct(EntityManager $em, PathResolver $pathResolver)
    {
        $this->em = $em;
        $this->pathResolver = $pathResolver;
    }

    /**
     * @param string $mountPath
     *
     * @return string
     */
    protected function getCachePath($mountPath)
    {
        $resolvedPath = $this->pathResolver->resolve($mountPath);
        $fs = new Filesystem();
        if (!$fs->exists($resolvedPath)) {
            $fs->mkdir($resolvedPath);
        }

        return $resolvedPath;
    }
}
