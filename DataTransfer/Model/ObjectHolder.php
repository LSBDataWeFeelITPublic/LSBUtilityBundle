<?php

namespace LSB\UtilityBundle\DataTransfer\Model;

class ObjectHolder
{
    /**
     * @param int|string|null $id id, uuid or other identifier
     * @param object|array $object entity,input dto, output dto or other object
     */
    public function __construct(
        protected int|string|null $id,
        protected object|array    $object
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
    public function getObject(): object|array
    {
        return $this->object;
    }

    /**
     * @param object $object
     * @return ObjectHolder
     */
    public function setObject(object|array $object): ObjectHolder
    {
        $this->object = $object;
        return $this;
    }
}