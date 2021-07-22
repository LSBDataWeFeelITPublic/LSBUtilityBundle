<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Calculation;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait CalculationTypeTrait
 * @package LSB\UtilityBundle\Calculation
 */
trait CalculationTypeTrait
{
    /**
     * @var int
     * @ORM\Column(type="integer", options={"default": 10})
     */
    protected int $calculationType = CalculationTypeInterface::CALCULATION_TYPE_NET;


    /**
     * @var int
     * @ORM\Column(type="integer", options={"default": 10})
     */
    protected int $vatCalculationType = CalculationTypeInterface::VAT_CALCULATION_TYPE_ADD;

    /**
     * @return int
     */
    public function getCalculationType(): int
    {
        return $this->calculationType;
    }

    /**
     * @param int $calculationType
     * @return static
     */
    public function setCalculationType(int $calculationType): static
    {
        $this->calculationType = $calculationType;
        return $this;
    }

    /**
     * @return int
     */
    public function getVatCalculationType(): int
    {
        return $this->vatCalculationType;
    }

    /**
     * @param int $vatCalculationType
     * @return $this
     */
    public function setVatCalculationType(int $vatCalculationType): static
    {
        $this->vatCalculationType = $vatCalculationType;
        return $this;
    }
}