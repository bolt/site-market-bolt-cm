<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Silex\Application;

/**
 * Abstract 'Action' class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
abstract class AbstractAction implements ActionInterface
{
    /** @var Application */
    private $app;

    /**
     * Constructor.
     *
     * @TODO We don't need app for live
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getAppService($name)
    {
        return $this->app[$name];
    }
}
