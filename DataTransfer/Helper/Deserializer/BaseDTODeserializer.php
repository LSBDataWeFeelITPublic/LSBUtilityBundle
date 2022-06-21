<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\Deserializer;

abstract class BaseDTODeserializer implements DTODeserializerInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return static::class;
    }
}