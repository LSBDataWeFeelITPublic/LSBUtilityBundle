<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Interfaces;

/**
 * Interface PositionInterface
 * @package LSB\UtilityBundle\Interfaces
 */
interface PositionInterface extends IdInterface
{
    /**
     * @return int|null
     */
    public function getPosition(): ?int;

    /**
     * @param int|null $position
     * @return $this
     */
    public function setPosition(?int $position): self;
}