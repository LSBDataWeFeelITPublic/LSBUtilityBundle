<?php

namespace LSB\UtilityBundle\DTO\DataTransformer;

use LSB\UtilityBundle\Module\ModuleInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

abstract class BaseDataTransformer implements DataTransformerInterface
{
    protected ?PropertyAccessor $propertyAccessor = null;

    public function getName(): string
    {
        return static::class;
    }

    public function getAdditionalName(): string
    {
        return ModuleInterface::ADDITIONAL_NAME_DEFAULT;
    }

    /**
     * @return \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    public function getPropertyAccessor(): PropertyAccessor
    {
        if ($this->propertyAccessor) {
            return $this->propertyAccessor;
        }

        return new PropertyAccessor();
    }
}