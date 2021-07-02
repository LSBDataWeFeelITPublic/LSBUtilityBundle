<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Token;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trait UnmaskTokenTrait
 * @package LSB\UtilityBundle\Token
 */
trait UnmaskTokenTrait
{
    /**
     * @var bool
     */
    protected bool $isDataMasked = false;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=120, nullable=true)
     * @Assert\Length(max="120")
     */
    protected ?string $unmaskToken;

    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\DateTime
     */
    protected ?DateTime $unmaskTokenGeneratedAt;

    /**
     * @return string|null
     */
    public function getUnmaskToken(): ?string
    {
        return $this->unmaskToken;
    }

    /**
     * @param string|null $unmaskToken
     * @return $this
     */
    public function setUnmaskToken(?string $unmaskToken): static
    {
        $this->unmaskToken = $unmaskToken;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getUnmaskTokenGeneratedAt(): ?DateTime
    {
        return $this->unmaskTokenGeneratedAt;
    }

    /**
     * @param DateTime|null $unmaskTokenGeneratedAt
     * @return $this
     */
    public function setUnmaskTokenGeneratedAt(?DateTime $unmaskTokenGeneratedAt): static
    {
        $this->unmaskTokenGeneratedAt = $unmaskTokenGeneratedAt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDataMasked(): bool
    {
        return $this->isDataMasked;
    }

    /**
     * @param bool $isDataMasked
     * @return $this
     */
    public function setIsDataMasked(bool $isDataMasked): static
    {
        $this->isDataMasked = $isDataMasked;
        return $this;
    }
}