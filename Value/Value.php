<?php
namespace LSB\UtilityBundle\Value;

/**
 * Class IntValue
 * @package LSB\UtilityBundle\Value
 */
class Value
{
    const UNIT_PCS = 'pcs';
    const UNIT_PERCENTAGE = '%';

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
        $this->amount = (string) intval($amount);
        $this->unit = $unit;
        $this->precision = $precision;
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
    public function getFloatAmount(): ?float
    {
        if ($this->amount === null || $this->precision === null) {
            return null;
        }

        $multipier = pow(10, $this->precision);
        
        return round($this->amount / $multipier, $this->getPrecision());
    }

    /**
     * @param string $amount
     * @return Value
     */
    public function setAmount(mixed $amount): Value
    {
        $this->amount = (string) intval($amount);
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
}