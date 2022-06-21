<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\Serializer;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use LSB\UtilityBundle\Controller\BaseCRUDApiController;
use LSB\UtilityBundle\DataTransfer\Request\RequestData;
use LSB\UtilityBundle\Service\ApiVersionGrabber;
use Symfony\Component\HttpFoundation\Request;

class JMSDTOSerializer extends BaseDTOSerializer
{
    const CONTENT_TYPE_DEFAULT = 'json';

    public function __construct(
        protected SerializerInterface $serializer,
        protected ApiVersionGrabber   $apiVersionGrabber,
    ) {
    }

    public function serialize(
        $result,
        Request $request,
        RequestData $requestData,
        string|int|null $apiVersionNumeric = null
    ): string {

        $apiVersionNumeric = $apiVersionNumeric ?? $this->apiVersionGrabber->getVersion($request, true);
        $context = new SerializationContext();
        $context
            ->setVersion($apiVersionNumeric)
            ->setSerializeNull(true);

        if (count($requestData->getSerializationGroups()) === 0) {
            $groups[] = BaseCRUDApiController::DEFAULT_SERIALIZATION_GROUP;
        } else {
            $groups = $requestData->getSerializationGroups();
        }

        $context->setGroups($groups);

        $result = $requestData->getResponseContent() ?? $result;

        return $this->serializer->serialize($result, $request->getContentType() ?? self::CONTENT_TYPE_DEFAULT, $context);
    }
}