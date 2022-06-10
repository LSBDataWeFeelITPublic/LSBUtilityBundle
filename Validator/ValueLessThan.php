<?php

namespace LSB\UtilityBundle\Validator;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ValueLessThan extends AbstractValueComparison
{
    public const TOO_HIGH_ERROR = '805e9f0c-eb1f-4edf-a27d-48b99118eaec';

    protected static $errorNames = [
        self::TOO_HIGH_ERROR => 'TOO_HIGH_ERROR',
    ];

    public $message = 'This value should be less than {{ compared_value }}.';
}
