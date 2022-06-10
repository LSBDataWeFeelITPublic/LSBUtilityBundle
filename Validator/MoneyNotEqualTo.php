<?php

namespace LSB\UtilityBundle\Validator;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class MoneyNotEqualTo extends AbstractMoneyComparison
{
    public const IS_EQUAL_ERROR = '27bda4bd-d8bc-4b01-b898-1b0e0924ab12';

    protected static $errorNames = [
        self::IS_EQUAL_ERROR => 'IS_EQUAL_ERROR',
    ];

    public $message = 'This value should not be equal to value {{ compared_value }}.';
}
