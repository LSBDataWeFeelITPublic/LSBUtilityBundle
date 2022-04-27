<?php

namespace LSB\UtilityBundle\DTO\Model;

interface DTOInterface
{
    public function getObject(): ?object;

    public function setObject(?object $entity);
}