<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Token;

use DateTime;

/**
 * Interface ViewTokenInterface
 * @package LSB\UtilityBundle\Token
 */
interface ViewTokenInterface
{
    /**
     * @return string|null
     */
    public function getViewToken(): ?string;

    /**
     * @param string|null $viewToken
     * @return ViewTokenTrait
     */
    public function setViewToken(?string $viewToken): self;

    /**
     * @return DateTime|null
     */
    public function getViewTokenGeneratedAt(): ?DateTime;

    /**
     * @param DateTime|null $viewTokenGeneratedAt
     * @return ViewTokenTrait
     */
    public function setViewTokenGeneratedAt(?DateTime $viewTokenGeneratedAt): self;
}