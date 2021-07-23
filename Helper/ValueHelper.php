<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Helper;

use Money\Currency;
use Money\Money;

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

    /**
     * @param int|null $amount
     * @param string|null $currencyIsoCode
     * @return Money|null
     */
    public static function intToMoney(?int $amount, ?string $currencyIsoCode): ?Money
    {
        if (!$currencyIsoCode) {
            return null;
        }
        if (!$amount) {
            return new Money(0, new Currency($currencyIsoCode));
        }

        return new Money($amount, new Currency($currencyIsoCode));
    }

    /**
     * @param Money|null $money
     * @return array
     */
    public static function moneyToInt(?Money $money): array
    {
        if ($money === null) {
            return [null, null];
        }

        return [$money->getAmount(), $money->getCurrency()];
    }
}