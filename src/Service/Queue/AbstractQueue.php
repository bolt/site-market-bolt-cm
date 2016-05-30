<?php

namespace Bolt\Extension\Bolt\MarketPlace\Service\Queue;

use Bolt\Configuration\ResourceManager;
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
    /** @var ResourceManager */
    protected $resourceManager;
    /** @var Composer */
    protected $composer;
    /** @var array */
    protected $config;

    /**
     * Constructor.
     *
     * @param EntityManager   $em
     * @param ResourceManager $resourceManager
     */
    public function __construct(EntityManager $em, ResourceManager $resourceManager)
    {
        $this->em = $em;
        $this->resourceManager = $resourceManager;
    }

    /**
     * @param string $mountPath
     *
     * @return string
     */
    protected function getCachePath($mountPath)
    {
        $resolvedPath = $this->resourceManager->getPath($mountPath);
        $fs = new Filesystem();
        if (!$fs->exists($resolvedPath)) {
            $fs->mkdir($resolvedPath);
        }

        return $resolvedPath;
    }
}
