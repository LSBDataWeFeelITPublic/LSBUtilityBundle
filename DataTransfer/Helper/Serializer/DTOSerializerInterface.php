<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\Serializer;

use LSB\UtilityBundle\DataTransfer\Request\RequestData;
use Symfony\Component\HttpFoundation\Request;

interface DTOSerializerInterface
{
    public function getName(): string;

    public function serialize(
        $result,
        Request $request,
        RequestData $requestData,
        string|int|null $apiVersionNumeric = null
    ): string;
}