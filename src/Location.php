<?php

namespace Bundle\Site\MarketPlace;

/**
 * Location constants.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Location
{
    const SATIS_LOCK = '%satis%/lock';
    const SATIS_QUEUE_WEBHOOK_PENDING = '%satis%/queue/webhook/pending';
    const SATIS_QUEUE_WEBHOOK_PROCESSED = '%satis%/queue/webhook/processed';
    const SATIS_QUEUE_PACKAGE = '%satis%/queue/package';

    /**
     * Constructor.
     */
    private function __construct()
    {
    }
}
