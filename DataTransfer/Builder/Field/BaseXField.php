<?php

namespace LSB\UtilityBundle\DataTransfer\Builder\Field;

abstract class BaseXField implements \JsonSerializable
{
    protected string $type;

    protected bool $isReadOnly = false;

    protected bool $isDisabled = false;

    public function __construct(
        bool $isReadOnly,
        bool $isDisabled
    ) {
        $this->isReadOnly = $isReadOnly;
        $this->isDisabled = $isDisabled;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => $this->type,
            'isReadOnly' => $this->isReadOnly,
            'isDisabled' => $this->isDisabled
        ];
    }
}