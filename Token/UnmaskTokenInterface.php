<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Token;

use DateTime;

/**
 * Interface UnmaskTokenInterface
 * @package LSB\UtilityBundle\Token
 */
interface UnmaskTokenInterface
{
    /**
     * @return string|null
     */
    public function getUnmaskToken(): ?string;

    /**
     * @param string|null $unmaskToken
     * @return $this
     */
    public function setUnmaskToken(?string $unmaskToken): self;

    /**
     * @return DateTime|null
     */
    public function getUnmaskTokenGeneratedAt(): ?DateTime;

    /**
     * @param DateTime|null $unmaskTokenGeneratedAt
     * @return $this
     */
    public function setUnmaskTokenGeneratedAt(?DateTime $unmaskTokenGeneratedAt): self;

    /**
     * @return bool
     */
    public function isDataMasked(): bool;

    /**
     * @param bool $isDataMasked
     * @return $this
     */
    public function setIsDataMasked(bool $isDataMasked): self;
}