<?php

namespace Bolt\Extension\Bolt\MarketPlace\Service\Queue;

use Bolt\Configuration\ResourceManager;
use Bolt\Extension\Bolt\MarketPlace\Service\SatisManager;
use Bolt\Storage\EntityManager;
use Composer\IO\NullIO;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Queue manager.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class QueueManager
{
    const CACHE_DIR_LOCK = 'cache/.satis/lock';

    /** @var EntityManager */
    protected $em;
    /** @var ResourceManager */
    protected $resourceManager;
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
     * @param SatisManager         $satisManager
     * @param OutputInterface|null $output
     */
    public function processQueues(SatisManager $satisManager, OutputInterface $output = null)
    {
        if ($output === null) {
            $output = new NullIO();
        }

        $this->getWebhooksQueue()->process($output);
        $this->getPackagesQueue()->process($satisManager, $output);
    }

    /**
     * @return WebhookQueue
     */
    protected function getWebhooksQueue()
    {
        return new WebhookQueue($this->em, $this->resourceManager);
    }

    /**
     * @return PackageQueue
     */
    protected function getPackagesQueue()
    {
        return new PackageQueue($this->em, $this->resourceManager);
    }
}
