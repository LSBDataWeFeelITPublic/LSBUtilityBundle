<?php

namespace LSB\UtilityBundle\DataTransfer\Model\Input;

use LSB\UtilityBundle\DataTransfer\Model\DTOInterface;

interface InputDTOInterface extends DTOInterface
{
    const ERROR_GLOBAL = 'global';
    const ERROR_DESERIALIZATION = 'deserialization';
}