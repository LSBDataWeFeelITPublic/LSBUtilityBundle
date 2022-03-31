<?php

namespace LSB\UtilityBundle\DTO\Model\Input;

use LSB\UtilityBundle\DTO\Model\DTOInterface;

interface InputDTOInterface extends DTOInterface
{
    public function postValidation(): void;
}