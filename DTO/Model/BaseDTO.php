<?php

namespace LSB\UtilityBundle\DTO\Model;

class BaseDTO
{
    /**
     * @var object|null
     */
    protected ?object $entity = null;

    /**
     * @var array
     */
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
     * @return object|null
     */
    public function getEntity(): ?object
    {
        return $this->entity;
    }

    /**
     * @param object|null $entity
     * @return \LSB\UtilityBundle\DTO\Model\BaseDTO
     */
    public function setEntity(?object $entity): BaseDTO
    {
        $this->entity = $entity;
        return $this;
    }
}