<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\ModuleInventory;

use LSB\UtilityBundle\Module\ModuleInterface;

/**
 * Class BaseManager
 * @package LSB\UtilityBundle\Service
 */
abstract class BaseModuleInventory implements ModuleInventoryInterface
{
    protected array $modules;

    /**
     * @param ModuleInterface $module
     */
    public function addModule(ModuleInterface $module): void
    {
        $this->modules[$module->getName()] = $module;
    }

    /**
     * @param string $moduleName
     * @param bool $throwException
     * @return ModuleInterface|null
     * @throws \Exception
     */
    public function getModuleByName(string $moduleName, bool $throwException = true): ?ModuleInterface
    {
        if (array_key_exists($moduleName, $this->modules)
            && $this->modules[$moduleName] instanceof ModuleInterface
        ) {
            return $this->modules[$moduleName];
        }

        if ($throwException) {
            throw new \Exception(sprintf('Module %s does not exist.', $moduleName));
        }

        return null;
    }

    /**
     * @param string $className
     * @param bool $throwException
     * @return ModuleInterface|null
     * @throws \Exception
     */
    public function getModuleByClass(string $className, bool $throwException = true): ?ModuleInterface
    {
        foreach ($this->modules as $module) {
            if ($module instanceof ModuleInterface && $module instanceof $className) {
                return $module;
            }
        }

        if ($throwException) {
            throw new \Exception(sprintf('Module %s does not exist.', $className));
        }

        return null;
    }
}