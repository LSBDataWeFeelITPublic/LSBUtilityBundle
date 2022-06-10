<?php

namespace LSB\UtilityBundle\Validator;

use LSB\UtilityBundle\Value\Value;

class ValueGreaterThanValidator extends AbstractValueComparisonValidator
{
    /**
     * {@inheritdoc}
     */
    protected function compareValues(?Value $value1, ?Value $value2)
    {
        return null === $value2 || $value1 && $value1->greaterThan($value2);
    }

    /**
     * {@inheritdoc}
     */
    protected function getErrorCode()
    {
        return ValueGreaterThan::TOO_LOW_ERROR;
    }
}
