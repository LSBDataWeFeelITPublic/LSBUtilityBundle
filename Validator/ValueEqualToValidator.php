<?php

namespace LSB\UtilityBundle\Validator;

use LSB\UtilityBundle\Value\Value;

class ValueEqualToValidator extends AbstractValueComparisonValidator
{
    /**
     * {@inheritdoc}
     */
    protected function compareValues(?Value $value1, ?Value $value2)
    {
        if ($value1 && $value2) {
            return $value1->equals($value2);
        }

        return $value1 === $value2;
    }

    /**
     * {@inheritdoc}
     */
    protected function getErrorCode()
    {
        return ValueEqualTo::NOT_EQUAL_ERROR;
    }
}
