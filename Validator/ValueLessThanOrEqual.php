<?php

namespace LSB\UtilityBundle\Validator;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ValueLessThanOrEqual extends AbstractValueComparison
{
    public const TOO_HIGH_ERROR = '22b145bc-3705-4806-a387-007eaa865f98';

    protected static $errorNames = [
        self::TOO_HIGH_ERROR => 'TOO_HIGH_ERROR',
    ];

    public $message = 'This value should be less than or equal to {{ compared_value }}.';
}
