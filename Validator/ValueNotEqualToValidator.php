<?php

namespace LSB\UtilityBundle\Validator;

use LSB\UtilityBundle\Value\Value;
use Symfony\Component\Validator\Constraints\NotEqualTo;

class ValueNotEqualToValidator extends AbstractValueComparisonValidator
{
    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    protected function compareValues(?Value $value1, ?Value $value2)
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
