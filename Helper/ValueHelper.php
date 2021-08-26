<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Helper;

use Alcohol\ISO4217;
use LSB\UtilityBundle\Value\Value;
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
        return is_null($value) ? null : (float)$value;
    }

    /**
     * @param float|string|null $value
     * @return string|null
     */
    public static function toString(float|string|null $value): ?string
    {
        return is_null($value) ? null : (string)$value;
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
    public static function moneyToIntCurrency(?Money $money): array
    {
        if ($money === null) {
            return [null, null];
        }

        return [(int)$money->getAmount(), (string)$money->getCurrency()];
    }

    /**
     * @param Money|int|null $money
     * @return int|null
     */
    public static function moneyToInt(Money|int|null $money): ?int
    {
        if ($money instanceof Money) {
            return (int)$money->getAmount();
        }

        return $money;
    }

    /**
     * @param Value|null $value
     * @return array
     */
    public static function valueToIntUnit(?Value $value): array
    {
        if ($value === null) {
            return [null, null];
        }

        return [(int)$value->getAmount(), $value->getUnit() ? (string)$value->getUnit() : null];
    }

    /**
     * @param Value|int|null $value
     * @return int|null
     */
    public static function valueToInt(Value|int|null $value): ?int
    {
        if ($value instanceof Money) {
            return (int)$value->getAmount();
        }

        return $value;
    }

    /**
     * @param int|null $amount
     * @param string|null $unit
     * @param int $precision
     * @return Value|null
     */
    public static function intToValue(?int $amount, ?string $unit = null, int $precision = 2): ?Value
    {
        if ($amount === null) {
            return null;
        }

        return new Value($amount, $unit, $precision);
    }

    /**
     * @param $amount
     * @param string|null $unit
     * @param int $precision
     * @return Value|null
     */
    public static function convertToValue($amount, ?string $unit = null, int $precision = 2): ?Value
    {
        if ($amount === null) {
            return null;
        }

        $multipier = pow(10, $precision);
        return new Value((int)round($amount * $multipier), $unit, $precision);
    }

    /**
     * @param $amount
     * @param string $currencyIsoCode
     * @return Money|null
     * @throws \Exception
     */
    public static function convertToMoney($amount, string $currencyIsoCode): ?Money
    {
        if ($amount === null) {
            return null;
        }

        if ($amount instanceof Money) {
            $amount = (int) $amount->getAmount();
        } elseif ($amount instanceof Value) {
            $amount = (int) $amount->getAmount();
        }

        $precision = self::getCurrencyPrecision($currencyIsoCode);
        $multipier = pow(10, $precision);
        $currency = new Currency($currencyIsoCode);

        return new Money((int)round($amount * $multipier), $currency);
    }

    /**
     * @param string $currencyIsoCode
     * @return int
     * @throws \Exception
     */
    public static function getCurrencyPrecision(string $currencyIsoCode): int
    {
        $iso4217 = new ISO4217();
        $result = $iso4217->getByAlpha3($currencyIsoCode);

        if (!isset($result['exp'])) {
            throw new \Exception('Missing currency exp');
        }

        return (int)$result['exp'];
    }

    /**
     * @param int $precision
     * @return int
     */
    public static function get100Percents(int $precision): int
    {
        return (int) pow(100, $precision);
    }
}
