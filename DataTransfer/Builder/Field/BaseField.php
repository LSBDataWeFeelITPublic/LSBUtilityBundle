<?php

namespace LSB\UtilityBundle\DataTransfer\Builder\Field;

abstract class BaseField implements \JsonSerializable
{
    const TYPE = 'base';

    protected string $type;

    protected bool $isReadOnly = false;

    protected bool $isDisabled = false;

    protected array $options = [];

    public function __construct(
        bool $isReadOnly = false,
        bool $isDisabled = false,
        array $options = []
    ) {
        $this->isReadOnly = $isReadOnly;
        $this->isDisabled = $isDisabled;
        $this->options = $options;
        $this->type = static::TYPE;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => $this->type,
            'isReadOnly' => $this->isReadOnly,
            'isDisabled' => $this->isDisabled,
            'options' => $this->options
        ];
    }
}