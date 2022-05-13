<?php

namespace LSB\UtilityBundle\DataTransfer\Builder;


use LSB\UtilityBundle\DataTransfer\Builder\Field\BaseXField;

class XBuilder
{
    protected BaseXField $field;

    public function __construct(BaseXField $field)
    {
        $this->field = $field;
    }

    public function toArray(): array
    {
        return [
            'field' => $this->field
        ];
    }


}