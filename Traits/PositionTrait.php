<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait PositionTrait
 * @package LSB\UtilityBundle\Traits
 */
trait PositionTrait
{
    /**
     * Position
     *
     * @var integer|null
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $position;

    /**
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param int|null $position
     * @return $this
     */
    public function setPosition(?int $position): self
    {
        $this->position = $position;
        return $this;
    }



}