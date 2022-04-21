<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DTO\DataTransformer;

use LSB\UtilityBundle\ModuleInventory\BaseModuleInventory;

class DataTransformerModuleService
{
    public function __construct(
        protected DataTransformerModuleInventory $dataTransformerModuleInventory,
    ) {
    }

    /**
     * @param string $moduleName
     * @return \LSB\UtilityBundle\DTO\DataTransformer\DataTransformerInterface|null
     * @throws \Exception
     */
    public function getModuleByName(string $moduleName): ?DataTransformerInterface
    {
        $module = $this->dataTransformerModuleInventory->getModuleByName($moduleName);

        if ($module instanceof DataTransformerInterface) {
            return $module;
        }

        return null;
    }

    /**
     * @param $data
     * @param string $to
     * @param array $context
     * @return \LSB\UtilityBundle\DTO\DataTransformer\DataTransformerInterface|null
     */
    public function getDataTransformer($data, string $to, array $context = []): ?DataTransformerInterface
    {
        return $this->dataTransformerModuleInventory->getDataTransformer($data, $to, $context);
    }

    /**
     * @param $data
     * @param string $to
     * @param array $context
     * @return \LSB\UtilityBundle\DTO\DataTransformer\DataTransformerInterface|null
     */
    public function getDataInitializerTransformer($data, string $to, array $context = []): ?DataTransformerInterface
    {
        return $this->dataTransformerModuleInventory->getDataInitializerTransformer($data, $to, $context);
    }
}
