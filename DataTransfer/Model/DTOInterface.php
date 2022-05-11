<?php

namespace LSB\UtilityBundle\DataTransfer\Model;

interface DTOInterface
{
    public function getObject(): ?object;

    public function setObject(?object $entity);
}