<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\Validator;

use LSB\UtilityBundle\DataTransfer\Model\DTOInterface;

interface DTOValidatorInterface
{
    public function getName(): string;

    public function validate(DTOInterface $DTO): void;

    public function isValid(DTOInterface $DTO): bool;
}