<?php

namespace Bolt\Extension\Bolt\MarketPlace\Service;

use Bolt\Extension\Bolt\MarketPlace\Entity\Package;
use Exception;

/**
 * A wrapper class to fetch theme info from Boltthem.es.
 *
 * @author Ross Riley, riley.ross@gmail.com
 */

class BoltThemes
{
    public $defaults;

    public $api = [
        'theme' => 'http://boltthem.es/api/theme/%s',
    ];

    public function __construct($defaults = [])
    {
        foreach ($defaults as $k => $v) {
            $this->$k = $v;
        }
    }

    public function info(Package $theme)
    {
        if ($theme->getType() !== 'bolt-theme') {
            return false;
        }

        $url = sprintf($this->api['theme'], $theme->getId());

        $opts = ['http' =>
            [
                'method'  => 'GET',
                'timeout' => 3,
            ],
        ];

        $context  = stream_context_create($opts);

        try {
            $result = json_decode(file_get_contents($url, false, $context), true);
        } catch (Exception $e) {
            return false;
        }

        if ($result['data']) {
            return $result['data'];
        }

        return false;
    }
}
