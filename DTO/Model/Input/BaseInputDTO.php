<?php

namespace LSB\UtilityBundle\DTO\Model\Input;

use LSB\UtilityBundle\DTO\Model\BaseDTO;

abstract class BaseInputDTO extends BaseDTO implements InputDTOInterface
{
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
}