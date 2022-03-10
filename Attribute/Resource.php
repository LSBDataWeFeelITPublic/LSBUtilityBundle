<?php

namespace LSB\UtilityBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_ALL)]
class Resource
{
    const TYPE_DESERIALIZE = 1;
    const TYPE_DATATRANSFORMER = 2;

    public function __construct(
        protected ?string $entityClass = null,
        protected ?string $managerClass = null,
        protected ?string $inputCreateDTOClass = null,
        protected ?string $inputUpdateDTOClass = null,
        protected ?string $outputDTOClass = null,
        protected ?int    $deserializationType = self::TYPE_DESERIALIZE
    ) {
    }

    /**
     * @return string|null
     */
    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }

    /**
     * @param string|null $entityClass
     * @return Resource
     */
    public function setEntityClass(?string $entityClass): Resource
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getManagerClass(): ?string
    {
        return $this->managerClass;
    }

    /**
     * @param string|null $managerClass
     * @return Resource
     */
    public function setManagerClass(?string $managerClass): Resource
    {
        $this->managerClass = $managerClass;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInputCreateDTOClass(): ?string
    {
        return $this->inputCreateDTOClass;
    }

    /**
     * @param string|null $inputCreateDTOClass
     * @return Resource
     */
    public function setInputCreateDTOClass(?string $inputCreateDTOClass): Resource
    {
        $this->inputCreateDTOClass = $inputCreateDTOClass;
        return $this;
    }

    /**
     * @param bool $checkCreate
     * @return string|null
     */
    public function getInputUpdateDTOClass(bool $checkCreate = true): ?string
    {
        if ($checkCreate && !$this->inputUpdateDTOClass) {
            return $this->getInputCreateDTOClass();
        }

        return $this->inputUpdateDTOClass;
    }

    /**
     * @param string|null $inputUpdateDTOClass
     * @return Resource
     */
    public function setInputUpdateDTOClass(?string $inputUpdateDTOClass): Resource
    {
        $this->inputUpdateDTOClass = $inputUpdateDTOClass;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOutputDTOClass(): ?string
    {
        return $this->outputDTOClass;
    }

    /**
     * @param string|null $outputDTOClass
     * @return Resource
     */
    public function setOutputDTOClass(?string $outputDTOClass): Resource
    {
        $this->outputDTOClass = $outputDTOClass;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getDeserializationType(): ?int
    {
        return $this->deserializationType;
    }

    /**
     * @param int|null $deserializationType
     * @return Resource
     */
    public function setDeserializationType(?int $deserializationType): Resource
    {
        $this->deserializationType = $deserializationType;
        return $this;
    }
}