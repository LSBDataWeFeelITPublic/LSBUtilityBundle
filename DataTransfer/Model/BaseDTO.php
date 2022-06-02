<?php

namespace LSB\UtilityBundle\DataTransfer\Model;

use LSB\UtilityBundle\DataTransfer\Model\Input\BaseInputDTO;

class BaseDTO
{
    protected bool $isNewObjectCreated = false;

    protected array $errors = [];

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isValid(): bool
    {
        return count($this->errors) === 0;
    }

    /**
     * @param $key
     * @param $error
     * @return $this
     */
    public function addError($key, $error): BaseDTO
    {
        $this->errors[$key][] = $error;

        return $this;
    }

    /**
     * @param array $errors
     * @return $this
     */
    public function setErrors(array $errors): BaseDTO
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * @var object|null
     */
    protected ?object $object = null;

    /**
     * @return object|null
     */
    public function getObject(): ?object
    {
        return $this->object;
    }

    /**
     * @param object|null $object
     * @return \LSB\UtilityBundle\DataTransfer\Model\BaseDTO
     */
    public function setObject(?object $object): BaseDTO
    {
        $this->object = $object;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNewObjectCreated(): bool
    {
        return $this->isNewObjectCreated;
    }

    /**
     * @param bool $isNewObjectCreated
     * @return BaseDTO
     */
    public function setIsNewObjectCreated(bool $isNewObjectCreated): BaseDTO
    {
        $this->isNewObjectCreated = $isNewObjectCreated;
        return $this;
    }
}