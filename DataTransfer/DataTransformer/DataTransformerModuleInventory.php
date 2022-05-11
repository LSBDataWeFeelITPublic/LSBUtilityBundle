<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DataTransfer\DataTransformer;

use LSB\UtilityBundle\ModuleInventory\BaseModuleInventory;

class DataTransformerModuleInventory extends BaseModuleInventory
{
    /**
     * @param $data
     * @param string $to
     * @param array $context
     * @return \LSB\UtilityBundle\DataTransfer\DataTransformer\DataTransformerInterface|null
     */
    public function getDataTransformer($data, string $to, array $context = []): ?DataTransformerInterface
    {
        foreach ($this->modules as $name => $modules) {
            /**
             * @var \LSB\UtilityBundle\DataTransfer\DataTransformer\DataTransformerInterface $module
             */
            foreach ($modules as $module) {
                if ($module->supportsTransformation($data, $to, $context)) {
                    return $module;
                }
            }

        }

        return null;
    }

    /**
     * @param $data
     * @param string $to
     * @param array $context
     * @return \LSB\UtilityBundle\DataTransfer\DataTransformer\DataInitializerTransformerInterface|null
     */
    public function getDataInitializerTransformer($data, string $to, array $context = []): ?DataInitializerTransformerInterface
    {
        /**
         * @var array $modules
         */
        foreach ($this->modules as $name => $modules) {
            /**
             * @var \LSB\UtilityBundle\DataTransfer\DataTransformer\DataInitializerTransformerInterface $module
             */
            foreach ($modules as $module) {
                if ($module instanceof DataInitializerTransformerInterface && $module->supportsTransformation($data, $to, $context)) {
                    return $module;
                }
            }

        }

        return null;
    }
}
