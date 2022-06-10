<?php

namespace LSB\UtilityBundle\Validator;

use Money\Money;

class MoneyGreaterThanOrEqualValidator extends AbstractMoneyComparisonValidator
{
    /**
     * {@inheritdoc}
     */
    protected function compareValues(?Money $value1, ?Money $value2)
    {
        return null === $value2 || $value1 && $value1->greaterThanOrEqual($value2);
    }

    /**
     * {@inheritdoc}
     */
    protected function getErrorCode()
    {
        return MoneyGreaterThanOrEqual::TOO_LOW_ERROR;
    }
}
