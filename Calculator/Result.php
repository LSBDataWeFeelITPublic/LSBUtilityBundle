<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Calculator;

//use LSB\LocaleBundle\Entity\CurrencyInterface;

/**
 * Class Result
 * @package LSB\UtilityBundle\Calculator
 */
class Result
{
//    /**
//     * @var CurrencyInterface
//     */
//    protected ?CurrencyInterface $currency;

    /**
     * @var float
     */
    protected float $totalNet;

    /**
     * @var float
     */
    protected float $totalGross;

    /**
     * @var bool
     */
    protected bool $isSuccess;

    /**
     * @var mixed
     */
    protected $subject;

    /**
     * @var array
     */
    protected array $calculationRes;

    /**
     * @var array
     */
    protected array $calculationProductRes;

    /**
     * @var array
     */
    protected array $calculationShippingRes;

    /**
     * @var array
     */
    protected array $calculationPaymentCostRes;


    public function __construct(
        bool $isSuccess,
//        ?CurrencyInterface $currency,
        float $totalNetto,
        float $totalGross,
        $subject = null,
        array &$calculationRes = [],
        array &$calculationProductRes = [],
        array &$calculationShippingRes = [],
        array &$calculationPaymentCostRes = []
    ) {
        $this->isSuccess = $isSuccess;
//        $this->currency = $currency;
        $this->totalNet = $totalNetto;
        $this->totalGross = $totalGross;
        $this->subject = $subject;
        $this->calculationRes = $calculationRes;
        $this->calculationProductRes = $calculationProductRes;
        $this->calculationShippingRes = $calculationShippingRes;
        $this->calculationPaymentCostRes = $calculationPaymentCostRes;
    }

//    /**
//     * @return CurrencyInterface
//     */
//    public function getCurrency(): ?CurrencyInterface
//    {
//        return $this->currency;
//    }
//
//    /**
//     * @param CurrencyInterface|null $currency
//     * @return Result
//     */
//    public function setCurrency(?CurrencyInterface $currency): Result
//    {
//        $this->currency = $currency;
//        return $this;
//    }

    /**
     * @return float
     */
    public function getTotalNet(): float
    {
        return $this->totalNet;
    }

    /**
     * @param float $totalNet
     * @return Result
     */
    public function setTotalNet(float $totalNet): Result
    {
        $this->totalNet = $totalNet;
        return $this;
    }

    /**
     * @return float
     */
    public function getTotalGross(): float
    {
        return $this->totalGross;
    }

    /**
     * @param float $totalGross
     * @return Result
     */
    public function setTotalGross(float $totalGross): Result
    {
        $this->totalGross = $totalGross;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    /**
     * @param bool $isSuccess
     * @return Result
     */
    public function setIsSuccess(bool $isSuccess): Result
    {
        $this->isSuccess = $isSuccess;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     * @return Result
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return array
     */
    public function getCalculationRes(): array
    {
        return $this->calculationRes;
    }

    /**
     * @param array $calculationRes
     * @return Result
     */
    public function setCalculationRes(array $calculationRes): Result
    {
        $this->calculationRes = $calculationRes;
        return $this;
    }

    /**
     * @return array
     */
    public function getCalculationProductRes(): array
    {
        return $this->calculationProductRes;
    }

    /**
     * @param array $calculationProductRes
     * @return Result
     */
    public function setCalculationProductRes(array $calculationProductRes): Result
    {
        $this->calculationProductRes = $calculationProductRes;
        return $this;
    }

    /**
     * @return array
     */
    public function getCalculationShippingRes(): array
    {
        return $this->calculationShippingRes;
    }

    /**
     * @param array $calculationShippingRes
     * @return Result
     */
    public function setCalculationShippingRes(array $calculationShippingRes): Result
    {
        $this->calculationShippingRes = $calculationShippingRes;
        return $this;
    }

    /**
     * @return array
     */
    public function getCalculationPaymentCostRes(): array
    {
        return $this->calculationPaymentCostRes;
    }

    /**
     * @param array $calculationPaymentCostRes
     * @return Result
     */
    public function setCalculationPaymentCostRes(array $calculationPaymentCostRes): Result
    {
        $this->calculationPaymentCostRes = $calculationPaymentCostRes;
        return $this;
    }
}
