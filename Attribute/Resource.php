<?php

namespace LSB\UtilityBundle\Attribute;

use Attribute;

/**
 * Primary resource attribute. Required for the operation of the DTO kernel listener mechanism.
 * Use Resource attribute to configure listeners.
 */
#[Attribute(Attribute::TARGET_ALL)]
class Resource
{
    const TYPE_AUTO = 1;
    const TYPE_DATA_TRANSFORMER = 2;

    /**
     * Do not set default values
     *
     * @param string|null $objectClass Class of the object to which the conversion is carried out: input dto -> object -> output dto
     * @param string|null $managerClass Manager class that handles the object (only for entities)
     * @param string|null $inputCreateDTOClass Input DTO object class for POST request method (creating a new object)
     * @param string|null $inputUpdateDTOClass Input DTO object class for PUT / PATCH request method (update of existing object
     * @param string|null $outputDTOClass Output DTO class
     * @param int|null $serializationType Deserialization mode (automatic or data transformer)
     * @param int|null $collectionItemSerializationType Deserialization mode for nested collections (automatic or data transformer)
     * @param bool|null $isDisabled Totally blocks input/output listeners
     * @param bool|null $isCollection Designation of data type - collection
     * @param string|null $collectionOutputDTOClass The DTO's output class of the collection
     * @param string|null $collectionItemOutputDTOClass The DTO output class of the collection item
     * @param bool|null $isActionDisabled Blocking the action of working with an object (creating, updating)
     * @param bool|null $isSecurityCheckDisabled Disables permission verification (isGranted forced to true)
     * @param bool|null $isCRUD CRUD resource true|false
     * @param string|null $voterAction Voter action (custom action name)
     */
    public function __construct(
        protected ?string $objectClass = null,
        protected ?string $managerClass = null,
        protected ?string $inputDTOClass = null,
        protected ?string $inputCreateDTOClass = null,
        protected ?string $inputUpdateDTOClass = null,
        protected ?string $outputDTOClass = null,
        protected ?int    $serializationType = null,
        protected ?int    $collectionItemSerializationType = null,
        protected ?bool   $isDisabled = null,
        protected ?bool   $isCollection = null,
        protected ?string $collectionOutputDTOClass = null,
        protected ?string $collectionItemOutputDTOClass = null,
        protected ?bool   $isActionDisabled = null,
        protected ?bool   $isSecurityCheckDisabled = null,
        protected ?bool   $isCRUD = null,
        protected ?string $voterAction = null
    ) {
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
     * @return Resource
     */
    public function setObjectClass(?string $objectClass): Resource
    {
        $this->objectClass = $objectClass;
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
     * @param bool $checkCreate
     * @return string|null
     */
    public function getInputCreateDTOClass(bool $checkCreate = true): ?string
    {
        if ($checkCreate && !$this->inputCreateDTOClass) {
            return $this->getInputDTOClass();
        }

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
            return $this->getInputDTOClass();
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
     * @return bool|null
     */
    public function getIsDisabled(): ?bool
    {
        return $this->isDisabled;
    }

    /**
     * @param bool|null $isDisabled
     * @return Resource
     */
    public function setIsDisabled(?bool $isDisabled): Resource
    {
        $this->isDisabled = $isDisabled;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsCollection(): ?bool
    {
        return $this->isCollection;
    }

    /**
     * @param bool|null $isCollection
     * @return Resource
     */
    public function setIsCollection(?bool $isCollection): Resource
    {
        $this->isCollection = $isCollection;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSerializationType(): ?int
    {
        return $this->serializationType;
    }

    /**
     * @param int|null $serializationType
     * @return Resource
     */
    public function setSerializationType(?int $serializationType): Resource
    {
        $this->serializationType = $serializationType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCollectionOutputDTOClass(): ?string
    {
        return $this->collectionOutputDTOClass;
    }

    /**
     * @param string|null $collectionOutputDTOClass
     * @return Resource
     */
    public function setCollectionOutputDTOClass(?string $collectionOutputDTOClass): Resource
    {
        $this->collectionOutputDTOClass = $collectionOutputDTOClass;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCollectionItemSerializationType(): ?int
    {
        return $this->collectionItemSerializationType;
    }

    /**
     * @param int|null $collectionItemSerializationType
     * @return Resource
     */
    public function setCollectionItemSerializationType(?int $collectionItemSerializationType): Resource
    {
        $this->collectionItemSerializationType = $collectionItemSerializationType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCollectionItemOutputDTOClass(): ?string
    {
        return $this->collectionItemOutputDTOClass;
    }

    /**
     * @param string|null $collectionItemOutputDTOClass
     * @return Resource
     */
    public function setCollectionItemOutputDTOClass(?string $collectionItemOutputDTOClass): Resource
    {
        $this->collectionItemOutputDTOClass = $collectionItemOutputDTOClass;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsActionDisabled(): ?bool
    {
        return $this->isActionDisabled;
    }

    /**
     * @param bool|null $isActionDisabled
     * @return Resource
     */
    public function setIsActionDisabled(?bool $isActionDisabled): Resource
    {
        $this->isActionDisabled = $isActionDisabled;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsSecurityCheckDisabled(): ?bool
    {
        return $this->isSecurityCheckDisabled;
    }

    /**
     * @param bool|null $isSecurityCheckDisabled
     * @return Resource
     */
    public function setIsSecurityCheckDisabled(?bool $isSecurityCheckDisabled): Resource
    {
        $this->isSecurityCheckDisabled = $isSecurityCheckDisabled;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsCRUD(): ?bool
    {
        return $this->isCRUD;
    }

    /**
     * @param bool|null $isCRUD
     * @return Resource
     */
    public function setIsCRUD(?bool $isCRUD): Resource
    {
        $this->isCRUD = $isCRUD;
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
     * @return Resource
     */
    public function setVoterAction(?string $voterAction): Resource
    {
        $this->voterAction = $voterAction;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInputDTOClass(): ?string
    {
        return $this->inputDTOClass;
    }

    /**
     * @param string|null $inputDTOClass
     * @return Resource
     */
    public function setInputDTOClass(?string $inputDTOClass): Resource
    {
        $this->inputDTOClass = $inputDTOClass;
        return $this;
    }
}