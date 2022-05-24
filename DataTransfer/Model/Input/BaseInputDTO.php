<?php

namespace LSB\UtilityBundle\DataTransfer\Model\Input;

use LSB\UtilityBundle\DataTransfer\Builder\Field\HiddenField;
use LSB\UtilityBundle\DataTransfer\Builder\XBuilder;
use LSB\UtilityBundle\DataTransfer\Builder\XProperty;
use LSB\UtilityBundle\DataTransfer\Model\BaseDTO;

abstract class BaseInputDTO extends BaseDTO implements InputDTOInterface
{
    /**
     * @var array
     */
    #[XProperty(new XBuilder(new HiddenField()))]
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