<?php

namespace LSB\UtilityBundle\Validator;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class MoneyLessThan extends AbstractMoneyComparison
{
    public const TOO_HIGH_ERROR = '67b0c26d-0bbc-4461-9a67-55f0af5c2bd4';

    protected static $errorNames = [
        self::TOO_HIGH_ERROR => 'TOO_HIGH_ERROR',
    ];

    public $message = 'This value should be less than {{ compared_value }}.';
}
