<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\Validator;

use LSB\UtilityBundle\DataTransfer\Model\DTOInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class DefaultDTOValidator extends BaseDTOValidator
{
    public function validate(DTOInterface $DTO): void
    {
        /**
         * @var ConstraintViolationList $error
         */
        $errors = $this->validator->validate($DTO);

        /**
         * @var ConstraintViolation $error
         */
        foreach ($errors as $error) {
            $DTO->addError($error->getPropertyPath(), $error->getMessage());
        }
    }
}