<?php

namespace LSB\UtilityBundle\Validator;

use Money\Money;

class MoneyGreaterThanValidator extends AbstractMoneyComparisonValidator
{
    /**
     * {@inheritdoc}
     */
    protected function compareValues(?Money $value1, ?Money $value2)
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
