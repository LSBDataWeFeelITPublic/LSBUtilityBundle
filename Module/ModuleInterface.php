<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Module;

/**
 * Interface ModuleInterface
 * @package LSB\UtilityBundle\ModuleInventory
 */
interface ModuleInterface
{
    /**
     * @return string
     */
    public function getName(): string;
}