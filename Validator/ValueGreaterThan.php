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
class ValueGreaterThan extends AbstractValueComparison
{
    public const TOO_LOW_ERROR = '7e6c67b6-9f9e-43f9-9e29-ac28db4532ae';

    protected static $errorNames = [
        self::TOO_LOW_ERROR => 'TOO_LOW_ERROR',
    ];

    public $message = 'This value should be greater than {{ compared_value }}.';
}
