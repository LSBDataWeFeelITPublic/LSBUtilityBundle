<?php

namespace LSB\UtilityBundle\Validator;

use LSB\UtilityBundle\Value\Value;

/**
 * Validates values are less than the previous (<).
 */
class ValueLessThanValidator extends AbstractValueComparisonValidator
{
    /**
     * {@inheritdoc}
     */
    protected function compareValues(?Value $value1, ?Value $value2)
    {
        return null === $value2 || $value1 && $value1->lessThan($value2);
    }

    /**
     * {@inheritdoc}
     */
    protected function getErrorCode()
    {
        return ValueLessThan::TOO_HIGH_ERROR;
    }
}
