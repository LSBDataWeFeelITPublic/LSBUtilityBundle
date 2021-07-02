<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Token;

use DateTime;

/**
 * Interface ConfirmationTokenInterface
 * @package LSB\UtilityBundle\Token
 */
interface ConfirmationTokenInterface
{
    /**
     * @param string|null $confirmationToken
     * @return $this
     */
    public function setConfirmationToken(?string $confirmationToken): self;

    /**
     * @return DateTime|null
     */
    public function getConfirmationTokenGeneratedAt(): ?DateTime;

    /**
     * @param DateTime|null $confirmationTokenGeneratedAt
     * @return $this
     */
    public function setConfirmationTokenGeneratedAt(?DateTime $confirmationTokenGeneratedAt): self;

    /**
     * @return DateTime|null
     */
    public function getConfirmedAt(): ?DateTime;

    /**
     * @param DateTime|null $confirmedAt
     * @return $this
     */
    public function setConfirmedAt(?DateTime $confirmedAt): self;
}