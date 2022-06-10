<?php

namespace LSB\UtilityBundle\Validator;

use Money\Money;

class MoneyEqualToValidator extends AbstractMoneyComparisonValidator
{
    /**
     * {@inheritdoc}
     */
    protected function compareValues(?Money $value1, ?Money $value2)
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
