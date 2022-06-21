<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\Serializer;

abstract class BaseDTOSerializer implements DTOSerializerInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return static::class;
    }
}