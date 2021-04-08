<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Interfaces;

/**
 * Interface IdInterface
 * @package LSB\UtilityBundle\Interfaces
 */
interface IdInterface
{
    /**
     * @return int|null
     */
    public function getId(): ?int;
}