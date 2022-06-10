<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LSB\UtilityBundle\Validator;

use LSB\UtilityBundle\Value\Value;

/**
 * Validates values are less than or equal to the previous (<=).
 */
class ValueLessThanOrEqualValidator extends AbstractValueComparisonValidator
{
    /**
     * {@inheritdoc}
     */
    protected function compareValues(?Value $value1, ?Value $value2)
    {
        return null === $value2 || $value1 && $value1->lessThanOrEqual($value2);
    }

    /**
     * {@inheritdoc}
     */
    protected function getErrorCode()
    {
        return ValueLessThanOrEqual::TOO_HIGH_ERROR;
    }
}
