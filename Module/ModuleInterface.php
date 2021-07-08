<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Module;

/**
 * Interface ModuleInterface
 * @package LSB\UtilityBundle\ModuleInventory
 */
interface ModuleInterface
{
    const ADDITIONAL_NAME_DEFAULT = 'default';

    /**
     * @return string
     */
    public function getName(): string;

    public function getAdditionalName(): string;
}