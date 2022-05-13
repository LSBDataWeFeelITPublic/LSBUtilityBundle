<?php

namespace LSB\UtilityBundle\DataTransfer\Builder\Field;

class AutocompleterField extends BaseField
{
    const TYPE = 'autocompleter';

    protected bool $allowCreate = false;

    public function __construct(
        bool  $allowCreate = false,
        bool  $isReadOnly = false,
        bool  $isDisabled = false,
        array $options = [],
    ) {
        parent::__construct($isReadOnly, $isDisabled, $options);
        $this->allowCreate = $allowCreate;
    }

    public function jsonSerialize(): mixed
    {
        $result = parent::jsonSerialize();
        $result['allowCreate'] = $this->allowCreate;

        return $result;
    }
}