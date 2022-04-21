<?php

namespace LSB\UtilityBundle\DTO\DataTransformer;

use LSB\UtilityBundle\Module\ModuleInterface;

abstract class BaseDataTransformer implements DataTransformerInterface
{
    public function getName(): string
    {
        return static::class;
    }

    public function getAdditionalName(): string
    {
        return ModuleInterface::ADDITIONAL_NAME_DEFAULT;
    }
}