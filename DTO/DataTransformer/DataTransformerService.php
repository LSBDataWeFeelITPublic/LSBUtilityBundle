<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DTO\DataTransformer;

use LSB\UtilityBundle\DTO\Model\Input\InputDTOInterface;
use LSB\UtilityBundle\DTO\Model\Output\OutputDTOInterface;
use LSB\UtilityBundle\Manager\BaseManager;
use LSB\UtilityBundle\ModuleInventory\BaseModuleInventory;

class DataTransformerService
{
    /**
     * @param \LSB\UtilityBundle\DTO\DataTransformer\DataTransformerModuleService $dataTransformerModuleManager
     */
    public function __construct(
        protected DataTransformerModuleService $dataTransformerModuleManager,
    ) {
    }

    /**
     * @param $data
     * @param string $to
     * @param array $context
     * @return \LSB\UtilityBundle\DTO\DataTransformer\DataTransformerInterface|null
     */
    public function getDataTransformer($data, string $to, array $context = []): ?DataTransformerInterface
    {
        return $this->dataTransformerModuleManager->getDataTransformer($data, $to, $context);
    }

    /**
     * @param $data
     * @param string $to
     * @param array $context
     * @return \LSB\UtilityBundle\DTO\DataTransformer\DataTransformerInterface|null
     */
    public function getDataInitializerTransformer($data, string $to, array $context = []): ?DataTransformerInterface
    {
        return $this->dataTransformerModuleManager->getDataInitializerTransformer($data, $to, $context);
    }

    /**
     * @param \LSB\UtilityBundle\DTO\Model\Input\InputDTOInterface $inputDTO
     * @param object $objectToPopulate
     * @param \LSB\UtilityBundle\Manager\BaseManager $manager
     * @return array
     */
    public function buildDataInitializerTransformerContext(
        InputDTOInterface $inputDTO,
        object $objectToPopulate,
        BaseManager $manager
    ): array {
        return [
            DataInitializerTransformerInterface::CONTEXT_INPUT_DTO => $inputDTO,
            DataInitializerTransformerInterface::CONTEXT_OBJECT_TO_POPULATE => $objectToPopulate,
            DataInitializerTransformerInterface::CONTEXT_OBJECT_MANAGER => $manager
        ];
    }

    /**
     * @param \LSB\UtilityBundle\DTO\Model\Input\InputDTOInterface|null $inputDTO
     * @param \LSB\UtilityBundle\DTO\Model\Output\OutputDTOInterface|null $outputDTO
     * @param object|null $objectToPopulate
     * @param \LSB\UtilityBundle\Manager\BaseManager|null $manager
     * @return array
     */
    public function buildDataTransformerContext(
        ?InputDTOInterface $inputDTO = null,
        ?OutputDTOInterface $outputDTO = null,
        ?object $objectToPopulate = null,
        ?BaseManager $manager = null
    ): array {
        return [
            DataInitializerTransformerInterface::CONTEXT_INPUT_DTO => $inputDTO,
            DataInitializerTransformerInterface::CONTEXT_OUTPUT_DTO => $outputDTO,
            DataInitializerTransformerInterface::CONTEXT_OBJECT_TO_POPULATE => $objectToPopulate,
            DataInitializerTransformerInterface::CONTEXT_OBJECT_MANAGER => $manager
        ];
    }
}
