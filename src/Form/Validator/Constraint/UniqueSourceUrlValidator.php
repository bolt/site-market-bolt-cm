<?php

namespace Bolt\Extension\Bolt\MarketPlace\Form\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Simple validator to check if a source address already exists.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class UniqueSourceUrlValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /** @var UniqueSourceUrl $constraint */
        $packageRepository = $constraint->getPackageRepository();
        $url = rtrim(str_replace('git@github.com:', 'https://github.com/', $value), '.git');
        $package = $packageRepository->findOneBy(['source' => $url]);

        if ($package === false) {
            $this->context->addViolation($constraint->message, ['%string%' => $value]);
        }
    }
}
