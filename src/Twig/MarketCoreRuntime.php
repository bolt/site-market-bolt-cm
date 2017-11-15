<?php

namespace Bundle\Site\MarketPlace\Twig;

use Bundle\Site\MarketPlace\Service;
use Bundle\Site\MarketPlace\Storage\Entity;
use forxer\Gravatar\Gravatar;
use Ramsey\Uuid\Uuid;

/**
 * Twig runtime class.
 *
 * @author Ross Riley <riley.ross@gmail.com>
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class MarketCoreRuntime
{
    /** @var Service\BoltThemes */
    protected $boltThemes;
    /** @var Service\PackageManager */
    protected $packageManager;
    /** @var Service\RecordManager */
    protected $recordManager;
    /** @var Service\Queue\QueueManager */
    protected $queueManager;
    /** @var Service\SatisManager */
    protected $satisManager;
    /** @var Service\Statistics */
    protected $statisticsManager;

    private $statusTemplate = '<div class="buildstatus ui icon label %s" data-content="%s"><i class="icon %s"></i> %s <span class="version">%s</span></div>';

    /**
     * Constructor.
     *
     * @param Service\BoltThemes         $boltThemes
     * @param Service\PackageManager     $packageManager
     * @param Service\RecordManager      $recordManager
     * @param Service\Queue\QueueManager $queueManager
     * @param Service\SatisManager       $satisManager
     * @param Service\Statistics         $statisticsManager
     */
    public function __construct(
        Service\BoltThemes $boltThemes,
        Service\PackageManager $packageManager,
        Service\RecordManager $recordManager,
        Service\Queue\QueueManager $queueManager,
        Service\SatisManager $satisManager,
        Service\Statistics $statisticsManager
    ) {
        $this->boltThemes = $boltThemes;
        $this->packageManager = $packageManager;
        $this->recordManager = $recordManager;
        $this->queueManager = $queueManager;
        $this->satisManager = $satisManager;
        $this->statisticsManager = $statisticsManager;
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
     * @return Entity\Package|object|false
     */
    public function getPackage($packageId)
    {
        if (Uuid::isValid($packageId)) {
            return $this->recordManager->getPackageById($packageId);
        }

        return $this->recordManager->getPackageByName($packageId);
    }

    /**
     * @param string $key
     *
     * @return array|false|string
     */
    public function getenv($key)
    {
        return getenv($key);
    }
}
