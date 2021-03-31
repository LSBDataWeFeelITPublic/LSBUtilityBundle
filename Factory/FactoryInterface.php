<?php

namespace LSB\UtilityBundle\Factory;

/**
 * Interface FactoryInterface
 */
interface FactoryInterface
{
    /**
     * FactoryInterface constructor.
     * @param string $className
     */
    public function __construct(string $className);

    /**
     * Creates new object
     *
     * @return mixed
     */
    public function createNew(): object;

    /**
     * Returns FQCN
     *
     * @return string
     */
    public function getClassName(): string;
}