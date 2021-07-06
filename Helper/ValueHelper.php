<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Helper;

/**
 * Class ValueHelper
 * @package LSB\UtilityBundle\Helper
 */
class ValueHelper
{
    /**
     * @param float|string|null $value
     * @return float|null
     */
    public static function toFloat(float|string|null $value): ?float
    {
        return is_null($value) ? null : (float) $value;
    }

    /**
     * @param float|string|null $value
     * @return string|null
     */
    public static function toString(float|string|null $value): ?string
    {
        return is_null($value) ? null : (string) $value;
    }
}