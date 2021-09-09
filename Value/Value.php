<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Value;

use Money\Calculator\BcMathCalculator;

final class Value
{
    const UNIT_PCS = 'pcs';
    const UNIT_PERCENTAGE = '%';

    public const ROUND_HALF_UP = PHP_ROUND_HALF_UP;

    public const ROUND_HALF_DOWN = PHP_ROUND_HALF_DOWN;

    public const ROUND_HALF_EVEN = PHP_ROUND_HALF_EVEN;

    public const ROUND_HALF_ODD = PHP_ROUND_HALF_ODD;

    public const ROUND_UP = 5;

    public const ROUND_DOWN = 6;

    /**
     * @var int
     */
    protected int $precision;

    /**
     * @var string
     */
    protected string $amount;

    /**
     * @var string|null
     */
    protected ?string $unit = null;

    /**
     * @param int $amount
     * @param string|null $unit
     * @param int $precision
     */
    public function __construct(mixed $amount, ?string $unit = null, int $precision = 2)
    {
        $this->amount = (string)intval($amount);
        $this->unit = $unit;
        $this->precision = $precision;
    }

    /**
     * @return static
     */
    public static function createZero(): self
    {
        return new self(0);
    }

//    /**
//     * @param int $amount
//     * @param string|null $unit
//     * @return Value
//     */
//    public static function quantity(int $amount, ?string $unit = 'pcs')
//    {
//        return new self($amount, $unit, 2);
//    }
//
//    /**
//     * @param int $amount
//     * @param string|null $unit
//     * @return Value
//     */
//    public static function discount(int $amount, ?string $unit = '%')
//    {
//        return new self($amount, $unit, 2);
//    }

    /**
     * @return bool
     */
    public function isZero(): bool
    {
        return BcMathCalculator::compare($this->amount, '0') === 0;
    }

    /**
     * @return int
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }

    /**
     * @param int $precision
     * @return Value
     */
    public function setPrecision(int $precision): Value
    {
        $this->precision = $precision;
        return $this;
    }

    /**
     * @return string
     */
    public function getAmount(): string
    {
        return $this->amount;
    }

    /**
     * @return float|null
     */
    public function getRealFloatAmount(): ?float
    {
        if ($this->amount === null || $this->precision === null) {
            return null;
        }

        $multipier = pow(10, $this->precision);

        return round($this->amount / $multipier, $this->getPrecision());
    }

    /**
     * @return string|null
     */
    public function getRealStringAmount(): ?string
    {
        $amount = $this->getRealFloatAmount();
        return is_null($amount) ? null : (string)$amount;
    }

    /**
     * @param string $amount
     * @return Value
     */
    public function setAmount(mixed $amount): Value
    {
        $this->amount = (string)intval($amount);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUnit(): ?string
    {
        return $this->unit;
    }

    /**
     * @param string|null $unit
     * @return Value
     */
    public function setUnit(?string $unit): Value
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * @param Value ...$addends
     * @return Value
     * @throws \Exception
     */
    public function add(Value ...$addends): Value
    {
        $amount = $this->amount;

        foreach ($addends as $addend) {
            if ($this->unit != $addend->unit || $this->precision != $addend->precision) {
                throw new \Exception('Unit & precision must be identical');
            }

            $amount = BcMathCalculator::add($amount, $addend->amount);
        }

        return new self($amount, $this->unit, $this->precision);
    }

    /**
     * @param Value ...$subtrahends
     * @return Value
     * @throws \Exception
     */
    public function subtract(Value ...$subtrahends): Value
    {
        $amount = $this->amount;

        foreach ($subtrahends as $subtrahend) {
            // Note: non-strict equality is intentional here, since `Currency` is `final` and reliable.
            if ($this->unit != $subtrahend->unit || $this->precision != $subtrahend->precision) {
                throw new \Exception('Unit must be identical');
            }

            $amount = BcMathCalculator::subtract($amount, $subtrahend->amount);
        }

        return new self($amount, $this->unit, $this->precision);
    }

    /**
     * @param int|string $multiplier
     * @param int $roundingMode
     * @return Value
     */
    public function multiply(int|string $multiplier, int $roundingMode = self::ROUND_HALF_UP): Value
    {
        if (is_int($multiplier)) {
            $multiplier = (string)$multiplier;
        }

        $product = $this->round(BcMathCalculator::multiply($this->amount, $multiplier), $roundingMode);

        return new self($product, $this->unit, $this->precision);
    }

    /**
     * @param int|string $divisor
     * @param int $roundingMode
     * @return Value
     */
    public function divide(int|string $divisor, int $roundingMode = self::ROUND_HALF_UP): Value
    {
        if (is_int($divisor)) {
            $divisor = (string)$divisor;
        }

        $quotient = $this->round(BcMathCalculator::divide($this->amount, $divisor), $roundingMode);

        return new self($quotient, $this->unit, $this->precision);
    }

    /**
     * @param string $amount
     * @param int $roundingMode
     * @return string
     */
    private function round(string $amount, int $roundingMode): string
    {
        if ($roundingMode === self::ROUND_UP) {
            return BcMathCalculator::ceil($amount);
        }

        if ($roundingMode === self::ROUND_DOWN) {
            return BcMathCalculator::floor($amount);
        }

        return BcMathCalculator::round($amount, $roundingMode);
    }

    /**
     * @param Value $other
     * @return bool
     * @throws \Exception
     */
    public function equals(Value $other): bool
    {
        if ($this->unit != $other->unit) {
            return false;
        }

        if ($this->amount === $other->amount) {
            return true;
        }

        return $this->compare($other) === 0;
    }

    /**
     * @param Value $other
     * @return int
     * @throws \Exception
     */
    public function compare(Value $other): int
    {
        // Note: non-strict equality is intentional here, since `Currency` is `final` and reliable.
        if ($this->unit != $other->unit) {
            throw new \Exception('Currencies must be identical');
        }

        return BcMathCalculator::compare($this->amount, $other->amount);
    }

    /**
     * Checks whether the value represented by this object is greater than the other.
     */
    public function greaterThan(Value $other): bool
    {
        return $this->compare($other) > 0;
    }

    public function greaterThanOrEqual(Value $other): bool
    {
        return $this->compare($other) >= 0;
    }

    /**
     * Checks whether the value represented by this object is less than the other.
     */
    public function lessThan(Value $other): bool
    {
        return $this->compare($other) < 0;
    }

    public function lessThanOrEqual(Value $other): bool
    {
        return $this->compare($other) <= 0;
    }
}