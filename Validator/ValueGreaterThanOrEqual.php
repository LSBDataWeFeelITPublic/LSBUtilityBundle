<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LSB\UtilityBundle\Validator;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ValueGreaterThanOrEqual extends AbstractValueComparison
{
    public const TOO_LOW_ERROR = '2fedd50d-bf3b-4a6d-b2a4-5ba9feec7af0';

    protected static $errorNames = [
        self::TOO_LOW_ERROR => 'TOO_LOW_ERROR',
    ];

    public $message = 'This value should be greater than or equal to {{ compared_value }}.';
}
