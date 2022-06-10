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
class MoneyGreaterThan extends AbstractMoneyComparison
{
    public const TOO_LOW_ERROR = 'ee9163eb-1b15-4e57-96b9-27f6b374d1eb';

    protected static $errorNames = [
        self::TOO_LOW_ERROR => 'TOO_LOW_ERROR',
    ];

    public $message = 'This value should be greater than {{ compared_value }}.';
}
