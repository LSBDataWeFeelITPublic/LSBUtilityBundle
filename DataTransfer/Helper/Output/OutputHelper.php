<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\Output;

use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\DataTransfer\Model\DTOInterface;
use LSB\UtilityBundle\DataTransfer\Model\Output\OutputDTOInterface;

class OutputHelper
{
    public static function verifyDTO(?OutputDTOInterface $outputDTO, Resource $resource): OutputDTOInterface
    {
        if (!$outputDTO) {
            if ($resource->getIsCollection()) {
                if (!$resource->getCollectionOutputDTOClass()) {
                    throw new \Exception('Missing output DTO class for collection.');
                }

                $outputDTO = new ($resource->getCollectionOutputDTOClass())();
            } else {
                if (!$resource->getOutputDTOClass()) {
                    throw new \Exception('Missing output DTO class.');
                }

                $outputDTO = new ($resource->getOutputDTOClass())();
            }
        }

        return $outputDTO;
    }

    /**
     * @throws \Exception
     */
    public static function createNewOutputDTOForService(
        ?OutputDTOInterface $outputDTO,
        Resource $resource,
        bool $isIterable,
        bool $isCollectionItem
    ): OutputDTOInterface {
        if ($isIterable) {
            if (!$outputDTO) {
                $outputDTO = new ($resource->getCollectionOutputDTOClass())();
            }
        } elseif ($isCollectionItem) {
            $outputDTO = new ($resource->getCollectionItemOutputDTOClass())();
        }

        if (!$outputDTO) {
            $outputDTO = new ($resource->getOutputDTOClass())();
        }

        if (!$outputDTO instanceof DTOInterface) {
            throw new \Exception('Output DTO class must implement OutputDTOInterface');
        }

        return $outputDTO;
    }
}