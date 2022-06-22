<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\Output;

use LSB\UtilityBundle\Attribute\Resource;
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
}