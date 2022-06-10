<?php

namespace LSB\UtilityBundle\Validator;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ValueEqualTo extends AbstractValueComparison
{
    public const NOT_EQUAL_ERROR = '253a1a39-9b12-415a-a59d-a468207dba19';

    protected static $errorNames = [
        self::NOT_EQUAL_ERROR => 'NOT_EQUAL_ERROR',
    ];

    public $message = 'This value should be equal to {{ compared_value }}.';
}
