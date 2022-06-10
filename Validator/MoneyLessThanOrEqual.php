<?php

namespace LSB\UtilityBundle\Validator;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class MoneyLessThanOrEqual extends AbstractMoneyComparison
{
    public const TOO_HIGH_ERROR = '33301951-3bd6-4bfb-94db-4869cee87d6a';

    protected static $errorNames = [
        self::TOO_HIGH_ERROR => 'TOO_HIGH_ERROR',
    ];

    public $message = 'This value should be less than or equal to {{ compared_value }}.';
}
