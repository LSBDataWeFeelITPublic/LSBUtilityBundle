<?php

namespace LSB\UtilityBundle\Validator;

use Money\Money;
use Symfony\Component\Validator\Constraints\NotEqualTo;

class MoneyNotEqualToValidator extends AbstractMoneyComparisonValidator
{
    /**
     * {@inheritdoc}
     */
    protected function compareValues(?Money $value1, ?Money $value2)
    {
        if ($value1 && $value2) {
            return !$value1->equals($value2);
        }

        return $value1 !== $value2;
    }

    /**
     * {@inheritdoc}
     */
    protected function getErrorCode()
    {
        return NotEqualTo::IS_EQUAL_ERROR;
    }
}
