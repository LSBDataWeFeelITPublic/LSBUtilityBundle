<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DataTransfer\Request;

class RequestIdentifier
{
    public function __construct(
        protected string     $identifierName,
        protected string|int $value,
        protected ?string    $identifierType = null
    ) {
    }

    /**
     * @return string
     */
    public function getIdentifierName(): string
    {
        return $this->identifierName;
    }

    /**
     * @return int|string
     */
    public function getValue(): int|string
    {
        return $this->value;
    }

    /**
     * @return string|null
     */
    public function getIdentifierType(): ?string
    {
        return $this->identifierType;
    }
}