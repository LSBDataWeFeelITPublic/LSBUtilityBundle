<?php

namespace LSB\UtilityBundle\DTO\Model\Input;

use LSB\UtilityBundle\DTO\Model\DTOInterface;

interface InputDTOInterface extends DTOInterface
{
    const ERROR_GLOBAL = 'global';
    const ERROR_DESERIALIZATION = 'deserialization';
}