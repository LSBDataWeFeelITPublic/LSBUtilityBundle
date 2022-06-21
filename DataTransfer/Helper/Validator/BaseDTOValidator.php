<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\Validator;

use LSB\UtilityBundle\DataTransfer\Model\DTOInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class BaseDTOValidator implements DTOValidatorInterface
{
    public function __construct(protected ValidatorInterface $validator){}

    /**
     * @return string
     */
    public function getName(): string
    {
        return static::class;
    }

    /**
     * @param \LSB\UtilityBundle\DataTransfer\Model\DTOInterface $DTO
     * @return void
     */
    abstract public function validate(DTOInterface $DTO): void;

    /**
     * @param \LSB\UtilityBundle\DataTransfer\Model\DTOInterface $DTO
     * @return bool
     */
    public function isValid(DTOInterface $DTO): bool
    {
        $this->validate($DTO);

        return $DTO->isValid();
    }
}