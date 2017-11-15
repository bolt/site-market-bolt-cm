<?php

namespace Bundle\Site\MarketPlace\Form\Validator\Constraint;

use Bundle\Site\MarketPlace\Storage\Repository;
use Symfony\Component\Validator\Constraint;

/**
 * Unique source URL constraint.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class UniqueSourceUrl extends Constraint
{
    public $message = 'The source URL "%string%" is already registered as an extension.';

    /** @var Repository\Package */
    private $packageRepository;

    public function __construct(Repository\Package $packageRepository, $options = null)
    {
        parent::__construct($options);

        $this->packageRepository = $packageRepository;
    }

    /**
     * @return Repository\Package
     */
    public function getPackageRepository()
    {
        return $this->packageRepository;
    }
}
