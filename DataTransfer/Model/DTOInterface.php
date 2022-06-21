<?php

namespace LSB\UtilityBundle\DataTransfer\Model;

interface DTOInterface
{
    public function getObject(): ?object;

    public function setObject(?object $entity);

    public function isNewObjectCreated(): bool;

    public function setIsNewObjectCreated(bool $isNewObjectCreated);

    public function getErrors(): array;

    public function isValid(): bool;
}