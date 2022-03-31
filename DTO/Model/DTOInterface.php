<?php

namespace LSB\UtilityBundle\DTO\Model;

interface DTOInterface
{
    public function getErrors(): array;

    public function addError($key, $error);

    public function getEntity(): ?object;

    public function setEntity(?object $entity);
}