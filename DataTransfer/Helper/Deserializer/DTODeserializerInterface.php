<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\Deserializer;

use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\DataTransfer\Model\Input\InputDTOInterface;
use Symfony\Component\HttpFoundation\Request;

interface DTODeserializerInterface
{
    public function getName(): string;

    public function deserialize(Request $request, Resource $resource, ?InputDTOInterface $existingDTO = null): ?InputDTOInterface;
}