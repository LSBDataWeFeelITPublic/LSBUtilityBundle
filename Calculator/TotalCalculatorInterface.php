<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Interfaces;

/**
 * Interface TotalCalculatorInterface
 * @package LSB\UtilBundle\Interfaces
 */
interface TotalCalculatorInterface
{
    /**
     * Zwraca nazwę kalkulator
     *
     * @return mixed
     */
    public function getName();

    /**
     * Zwraca nazwę klasy wspieranej przez kalkulator
     *
     * @return mixed
     */
    public function getSupportedClass();

    /**
     * @return mixed
     */
    public function getSupportedPositionClass();

    /**
     * @return mixed
     */
    public function getSupportedClassRepository();

    /**
     * @param array $attributes
     * @return mixed
     */
    public function setAttributes(array $attributes);

    /**
     * @return mixed
     */
    public function getSupportedPositionClassRepository();
}
