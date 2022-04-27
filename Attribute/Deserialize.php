<?php

namespace LSB\UtilityBundle\Attribute;

use Attribute;

/**
 * @deprecated Not used
 */
#[Attribute(Attribute::TARGET_ALL)]
class Deserialize
{
    const TYPE_MANUAL = 1;
    const TYPE_JMS = 2;
    const TYPE_DATATRANSFORMER = 3;

    const EXCEPTION_CATCH = false;
    const EXCEPTION_THROW = true;

    public function __construct(
        protected ?int $type,
        protected bool $throwException = false
    ) {}

    /**
     * @return int|null
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isThrowException(): bool
    {
        return $this->throwException;
    }
}