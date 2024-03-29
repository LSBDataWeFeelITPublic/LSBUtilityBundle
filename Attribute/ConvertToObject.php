<?php

namespace LSB\UtilityBundle\Attribute;

use Attribute;

/**
 * Attribute used to designate the input DTO property to be converted to an object.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ConvertToObject
{
    const KEY_ID = 10;
    const KEY_UUID = 20;

//    const SECURITY_LEVEL_PUBLIC = 10; //Object can be accessed publicly (via returned DTO object)
//    const SECURITY_LEVEL_PROTECTED = 20; //Object can be accessed after validation (via returned DTO object)
//    const SECURITY_LEVEL_PRIVATE = 30; //Object is available only for application (serialize

    /**
     * @param int $key Object primary key ID|UUID
     * @param string|null $managerClass Manager class dedicated to the specific entity object
     * @param string|null $voterAction Voter action
     * @param bool $throwNotFoundException
     * @param string|null $objectClass Entity FQCN
     * @param bool $isTranslation
     * @param bool $useObjectId
     * @param bool $createNewObject
     */
    public function __construct(
        protected int     $key = self::KEY_UUID,
        protected ?string $managerClass = null,
        protected ?string $voterAction = null,
        protected bool    $throwNotFoundException = false,
        protected ?string $objectClass = null,
        protected bool    $isTranslation = false,
        protected bool    $useObjectId = false,
        protected bool    $createNewObject = true
    ) {
    }

    /**
     * @return int
     */
    public function getKey(): int
    {
        return $this->key;
    }

    /**
     * @param int $key
     * @return ConvertToObject
     */
    public function setKey(int $key): ConvertToObject
    {
        $this->key = $key;
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
     * @return ConvertToObject
     */
    public function setManagerClass(?string $managerClass): ConvertToObject
    {
        $this->managerClass = $managerClass;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getVoterAction(): ?string
    {
        return $this->voterAction;
    }

    /**
     * @param string|null $voterAction
     * @return ConvertToObject
     */
    public function setVoterAction(?string $voterAction): ConvertToObject
    {
        $this->voterAction = $voterAction;
        return $this;
    }

    /**
     * @return bool
     */
    public function isThrowNotFoundException(): bool
    {
        return $this->throwNotFoundException;
    }

    /**
     * @param bool $throwNotFoundException
     * @return ConvertToObject
     */
    public function setThrowNotFoundException(bool $throwNotFoundException): ConvertToObject
    {
        $this->throwNotFoundException = $throwNotFoundException;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getObjectClass(): ?string
    {
        return $this->objectClass;
    }

    /**
     * @param string|null $objectClass
     * @return ConvertToObject
     */
    public function setObjectClass(?string $objectClass): ConvertToObject
    {
        $this->objectClass = $objectClass;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTranslation(): bool
    {
        return $this->isTranslation;
    }

    /**
     * @param bool $isTranslation
     * @return ConvertToObject
     */
    public function setIsTranslation(bool $isTranslation): ConvertToObject
    {
        $this->isTranslation = $isTranslation;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUseObjectId(): bool
    {
        return $this->useObjectId;
    }

    /**
     * @param bool $useObjectId
     * @return ConvertToObject
     */
    public function setUseObjectId(bool $useObjectId): ConvertToObject
    {
        $this->useObjectId = $useObjectId;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCreateNewObject(): bool
    {
        return $this->createNewObject;
    }

    /**
     * @param bool $createNewObject
     * @return ConvertToObject
     */
    public function setCreateNewObject(bool $createNewObject): ConvertToObject
    {
        $this->createNewObject = $createNewObject;
        return $this;
    }
}