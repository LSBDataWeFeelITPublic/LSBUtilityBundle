<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\ModuleInventory;

/**
 * Interface ModuleInventoryInterface
 * @package LSB\UtilityBundle\ModuleInventory
 */
interface ModuleInventoryInterface
{
    const SUBNAME = 'default';

    /**
     * @return array
     */
    public function getModules(): array;
}