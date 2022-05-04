<?php

namespace LSB\UtilityBundle\DTO\Model;

class BaseDTO
{
    /**
     * @var object|null
     */
    protected ?object $object = null;

    /**
     * @return object|null
     */
    public function getObject(): ?object
    {
        return $this->object;
    }

    /**
     * @param object|null $object
     * @return \LSB\UtilityBundle\DTO\Model\BaseDTO
     */
    public function setObject(?object $object): BaseDTO
    {
        $this->object = $object;
        return $this;
    }
}