<?php

namespace LSB\UtilityBundle\DataTransfer\Builder\Relation;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes\Schema;
use ReturnTypeWillChange;

class XRelation implements \JsonSerializable
{
    protected Schema $relation;

    public function __construct(string $outputDTO)
    {
        $this->relation = new Schema(new Model(type: $outputDTO));
    }

    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->relation;
    }
}