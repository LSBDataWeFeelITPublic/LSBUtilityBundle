<?php

namespace LSB\UtilityBundle\DataTransfer\Model;

class ObjectHolder
{
    public function __construct(
        //id, uuid or other identifier
        protected int|string|null $id,
        //entity,input dto, output dto or other object
        protected object          $object
    ) {
    }

    /**
     * @return int|string|null
     */
    public function getId(): int|string|null
    {
        return $this->key;
    }

    /**
     * @param int|string|null $id
     * @return ObjectHolder
     */
    public function setId(int|string|null $id): ObjectHolder
    {
        $this->key = $id;
        return $this;
    }

    /**
     * @return object
     */
    public function getObject(): object
    {
        return $this->object;
    }

    /**
     * @param object $object
     * @return ObjectHolder
     */
    public function setObject(object $object): ObjectHolder
    {
        $this->object = $object;
        return $this;
    }
}