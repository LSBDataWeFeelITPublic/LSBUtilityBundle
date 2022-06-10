<?php

namespace LSB\UtilityBundle\Validator;

use Money\Money;

class MoneyLessThanOrEqualValidator extends AbstractMoneyComparisonValidator
{
    /**
     * {@inheritdoc}
     */
    protected function compareValues(?Money $value1, ?Money $value2)
    {
        return null === $value2 || $value1 && $value1->lessThanOrEqual($value2);
    }

    /**
     * {@inheritdoc}
     */
    protected function getErrorCode()
    {
        return MoneyLessThanOrEqual::TOO_HIGH_ERROR;
    }
}
