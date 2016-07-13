<?php

namespace Bolt\Extension\Bolt\MarketPlace\Service\Queue;

use Bolt\Configuration\ResourceManager;
use Bolt\Extension\Bolt\MarketPlace\Service\SatisManager;
use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Storage\EntityManager;
use Composer\IO\NullIO;
use Pimple as Container;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Queue manager.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class QueueManager
{
    /** @var EntityManager */
    protected $em;
    /** @var ResourceManager */
    protected $resourceManager;
    /** @var Container */
    private $queues;

    /**
     * Constructor.
     *
     * @param EntityManager   $em
     * @param ResourceManager $resourceManager
     * @param Container       $queues
     */
    public function __construct(EntityManager $em, ResourceManager $resourceManager, Container $queues)
    {
        $this->em = $em;
        $this->resourceManager = $resourceManager;
        $this->queues = $queues;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function queueWebhook(Request $request)
    {
        return $this->getWebhooksQueue()->queue($request);
    }

    /**
     * @param Entity\Package $package
     */
    public function queuePackage(Entity\Package $package)
    {
        return $this->getPackagesQueue()->queue($package);
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
        return $this->queues['webhook'];
    }

    /**
     * @return PackageQueue
     */
    protected function getPackagesQueue()
    {
        return $this->queues['package'];
    }
}
