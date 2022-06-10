<?php

namespace LSB\UtilityBundle\Validator;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class MoneyEqualTo extends AbstractMoneyComparison
{
    public const NOT_EQUAL_ERROR = '89c762c1-86f3-430f-b482-ba1e9e623755';

    protected static $errorNames = [
        self::NOT_EQUAL_ERROR => 'NOT_EQUAL_ERROR',
    ];

    public $message = 'This value should be equal to {{ compared_value }}.';
}
