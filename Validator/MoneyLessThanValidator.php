<?php

namespace LSB\UtilityBundle\Validator;

use Money\Money;

class MoneyLessThanValidator extends AbstractMoneyComparisonValidator
{
    /**
     * {@inheritdoc}
     */
    protected function compareValues(?Money $value1, ?Money $value2)
    {
        return null === $value2 || $value1 && $value1->lessThan($value2);
    }

    /**
     * {@inheritdoc}
     */
    protected function getErrorCode()
    {
        return MoneyLessThan::TOO_HIGH_ERROR;
    }
}
