<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Token;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trait ConfirmationTokenTrait
 * @package LSB\UtilityBundle\Token
 */
trait ConfirmationTokenTrait
{
    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     * @Assert\Length(max="120")
     */
    protected ?string $confirmationToken;

    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\DateTime
     */
    protected ?DateTime $confirmationTokenGeneratedAt;

    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $confirmedAt;

    /**
     * @return string|null
     */
    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    /**
     * @param string|null $confirmationToken
     * @return $this
     */
    public function setConfirmationToken(?string $confirmationToken): static
    {
        $this->confirmationToken = $confirmationToken;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getConfirmationTokenGeneratedAt(): ?DateTime
    {
        return $this->confirmationTokenGeneratedAt;
    }

    /**
     * @param DateTime|null $confirmationTokenGeneratedAt
     * @return $this
     */
    public function setConfirmationTokenGeneratedAt(?DateTime $confirmationTokenGeneratedAt): static
    {
        $this->confirmationTokenGeneratedAt = $confirmationTokenGeneratedAt;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getConfirmedAt(): ?DateTime
    {
        return $this->confirmedAt;
    }

    /**
     * @param DateTime|null $confirmedAt
     * @return $this
     */
    public function setConfirmedAt(?DateTime $confirmedAt): static
    {
        $this->confirmedAt = $confirmedAt;
        return $this;
    }
}