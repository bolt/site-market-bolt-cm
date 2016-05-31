<?php

namespace Bolt\Extension\Bolt\MarketPlace\Service;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Storage\EntityManager;

/**
 * Record manager service.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class RecordManager
{
    /** @var EntityManager */
    protected $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $packageId
     *
     * @return object
     */
    public function getPackageById($packageId)
    {
        $repo = $this->em->getRepository(Entity\Package::class);

        return $repo->findOneBy(['id' => $packageId]);
    }
}