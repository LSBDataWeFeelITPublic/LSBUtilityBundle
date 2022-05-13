<?php

namespace LSB\UtilityBundle\DataTransfer\Builder;

use LSB\UtilityBundle\DataTransfer\Builder\Field\BaseField;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes\Schema;

class XBuilder
{
    protected BaseField $field;

    protected ?Schema $relation = null;

    public function __construct(
        BaseField $field,
        ?string   $relation = null
    ) {
        $this->field = $field;
        $this->relation = $relation ? new Schema(ref: new Model(type: $relation)) : null;
    }

    public function toArray(): array
    {
        return [
            'field' => $this->field,
            'relation' => $this->relation
        ];
    }
}