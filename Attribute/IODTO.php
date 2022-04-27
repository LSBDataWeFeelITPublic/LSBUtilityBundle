<?php

namespace LSB\UtilityBundle\Attribute;

use Attribute;

/**
 * @deprecated Not used anymore.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class DTO
{
    public function __construct(
        protected ?string $inputDTO,
        protected ?string $outputDTO
    ) {}
}