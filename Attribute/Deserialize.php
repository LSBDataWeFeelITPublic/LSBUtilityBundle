<?php

namespace LSB\UtilityBundle\Attribute;

use Attribute;

/**
 * The attribute marks the function that has no impact on the program state or passed parameters used after the function execution.
 * This means that a function call that resolves to such a function can be safely removed if the execution result is not used in code afterwards.
 *
 * @since 8.0
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