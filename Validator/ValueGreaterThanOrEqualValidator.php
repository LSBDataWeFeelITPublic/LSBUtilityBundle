<?php

namespace LSB\UtilityBundle\Validator;

use LSB\UtilityBundle\Value\Value;

class ValueGreaterThanOrEqualValidator extends AbstractValueComparisonValidator
{
    /**
     * {@inheritdoc}
     */
    protected function compareValues(?Value $value1, ?Value $value2)
    {
        return null === $value2 || $value1 && $value1->greaterThanOrEqual($value2);
    }

    /**
     * {@inheritdoc}
     */
    protected function getErrorCode()
    {
        return ValueGreaterThanOrEqual::TOO_LOW_ERROR;
    }
}
