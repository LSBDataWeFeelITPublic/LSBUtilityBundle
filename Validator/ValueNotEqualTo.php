<?php

namespace LSB\UtilityBundle\Validator;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ValueNotEqualTo extends AbstractValueComparison
{
    public const IS_EQUAL_ERROR = '7bb8036b-2aaa-4af6-bd06-b8dc9a9d360f';

    protected static $errorNames = [
        self::IS_EQUAL_ERROR => 'IS_EQUAL_ERROR',
    ];

    public $message = 'This value should not be equal to value {{ compared_value }}.';
}
