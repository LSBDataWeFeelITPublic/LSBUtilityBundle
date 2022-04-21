<?php

namespace LSB\UtilityBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DTOPropertyConfig
{
    public function __construct(
        protected ?string $objectGetter = null,
        protected ?string $objectSetter = null,
        protected ?string $DTOGetter = null,
        protected ?string $DTOSetter = null
    ) {
    }

    /**
     * @return string|null
     */
    public function getObjectGetter(): ?string
    {
        return $this->objectGetter;
    }

    /**
     * @param string|null $objectGetter
     * @return DTOPropertyConfig
     */
    public function setObjectGetter(?string $objectGetter): DTOPropertyConfig
    {
        $this->objectGetter = $objectGetter;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getObjectSetter(): ?string
    {
        return $this->objectSetter;
    }

    /**
     * @param string|null $objectSetter
     * @return DTOPropertyConfig
     */
    public function setObjectSetter(?string $objectSetter): DTOPropertyConfig
    {
        $this->objectSetter = $objectSetter;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDTOGetter(): ?string
    {
        return $this->DTOGetter;
    }

    /**
     * @param string|null $DTOGetter
     * @return DTOPropertyConfig
     */
    public function setDTOGetter(?string $DTOGetter): DTOPropertyConfig
    {
        $this->DTOGetter = $DTOGetter;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDTOSetter(): ?string
    {
        return $this->DTOSetter;
    }

    /**
     * @param string|null $DTOSetter
     * @return DTOPropertyConfig
     */
    public function setDTOSetter(?string $DTOSetter): DTOPropertyConfig
    {
        $this->DTOSetter = $DTOSetter;
        return $this;
    }
}