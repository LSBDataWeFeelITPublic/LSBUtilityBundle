<?php

namespace LSB\UtilityBundle\DTO\Model\Input;

use LSB\UtilityBundle\DTO\Model\BaseDTO;

abstract class BaseInputDTO extends BaseDTO implements InputDTOInterface
{
    public function postValidation(): void
    {

    }
}