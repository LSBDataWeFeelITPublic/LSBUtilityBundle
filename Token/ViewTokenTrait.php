<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Token;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trait ViewTokenTrait
 * @package LSB\UtilityBundle\Token
 */
trait ViewTokenTrait
{
    /**
     * @var string|null
     * @ORM\Column(type="string", length=120, nullable=true)
     * @Assert\Length(max="120")
     */
    protected ?string $viewToken;

    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\DateTime
     */
    protected ?DateTime $viewTokenGeneratedAt;

    /**
     * @return string|null
     */
    public function getViewToken(): ?string
    {
        return $this->viewToken;
    }

    /**
     * @param string|null $viewToken
     * @return ViewTokenTrait
     */
    public function setViewToken(?string $viewToken): static
    {
        $this->viewToken = $viewToken;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getViewTokenGeneratedAt(): ?DateTime
    {
        return $this->viewTokenGeneratedAt;
    }

    /**
     * @param DateTime|null $viewTokenGeneratedAt
     * @return ViewTokenTrait
     */
    public function setViewTokenGeneratedAt(?DateTime $viewTokenGeneratedAt): static
    {
        $this->viewTokenGeneratedAt = $viewTokenGeneratedAt;
        return $this;
    }
}