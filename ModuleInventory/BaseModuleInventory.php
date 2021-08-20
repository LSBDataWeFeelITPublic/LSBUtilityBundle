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
    const ADD_MODULE_METHOD = 'addModule';

    protected array $modules = [];

    /**
     * @param ModuleInterface $module
     */
    public function addModule(ModuleInterface $module): void
    {
        $this->modules[$module->getName()][$module->getAdditionalName()] = $module;
    }

    /**
     * @param string $moduleName
     * @param string $additionalName
     * @param bool $throwException
     * @return ModuleInterface|null
     * @throws \Exception
     */
    public function getModuleByName(
        string $moduleName,
        string $additionalName = ModuleInterface::ADDITIONAL_NAME_DEFAULT,
        bool $throwException = true
    ): ?ModuleInterface {
        if (array_key_exists($moduleName, $this->modules)
        ) {
            /**
             * @var ModuleInterface $module
             */
            foreach ($this->modules[$moduleName] as $module) {
                if ($module instanceof ModuleInterface && $module->getAdditionalName() === $additionalName) {
                    return $module;
                }
            }

            //return $this->modules[$moduleName];
        }

        if ($throwException) {
            throw new \Exception(sprintf('Module %s does not exist.', $moduleName));
        }

        return null;
    }

    /**
     * @param string $className
     * @param string $additionalName
     * @param bool $throwException
     * @return ModuleInterface|null
     * @throws \Exception
     */
    public function getModuleByClass(
        string $className,
        string $additionalName = ModuleInterface::ADDITIONAL_NAME_DEFAULT,
        bool $throwException = true
    ): ?ModuleInterface {
        /**
         * @var array $modules
         */
        foreach ($this->modules as $modules) {
            /**
             * @var ModuleInterface $module
             */
            foreach ($modules as $module) {
                if ($module instanceof ModuleInterface && $module instanceof $className && $module->getAdditionalName() === $additionalName) {
                    return $module;
                }
            }
        }

        if ($throwException) {
            throw new \Exception(sprintf('Module %s does not exist.', $className));
        }

        return null;
    }

    /**
     * @return array
     */
    public function getModules(): array
    {
        return $this->modules;
    }
}