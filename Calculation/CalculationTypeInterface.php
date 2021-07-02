<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Calculation;

/**
 * Interface CalculationTypeInterface
 * @package LSB\UtilityBundle\Calculation
 */
interface CalculationTypeInterface
{
    const CALCULATION_TYPE_NET = 10;
    const CALCULATION_TYPE_GROSS = 20;

    const VAT_CALCULATION_TYPE_ADD = 10;
    const VAT_CALCULATION_TYPE_EXEMPT = 20;


    /**
     * @return int
     */
    public function getCalculationType(): int;

    /**
     * @param int $calculationType
     * @return $this
     */
    public function setCalculationType(int $calculationType): self;

    /**
     * @return int
     */
    public function getVatCalculationType(): int;

    /**
     * @param int $vatCalculationType
     * @return $this
     */
    public function setVatCalculationType(int $vatCalculationType): self;
}