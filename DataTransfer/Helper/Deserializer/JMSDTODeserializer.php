<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\Deserializer;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\SerializerInterface;
use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\DataTransfer\Model\Input\InputDTOInterface;
use LSB\UtilityBundle\Serializer\ObjectConstructor\ExistingObjectConstructor;
use Symfony\Component\HttpFoundation\Request;

class JMSDTODeserializer extends BaseDTODeserializer
{
    public function __construct(protected SerializerInterface $serializer)
    {
    }

    public function deserialize(Request $request, Resource $resource, ?InputDTOInterface $existingDTO = null): ?InputDTOInterface
    {
        try {
            $context = DeserializationContext::create();
            if ($existingDTO) {
                $context->setAttribute(ExistingObjectConstructor::ATTRIBUTE_TARGET, $existingDTO);
            }

            return $this->serializer->deserialize(
                $request->getContent(),
                $resource->getInputCreateDTOClass(),
                $request->getFormat($request->headers->get('Content-Type')),
                $context
            );
        } catch (RuntimeException $e) {
            if (!$existingDTO) {
                $existingDTO = new ($resource->getInputUpdateDTOClass())();
                if (!$existingDTO instanceof InputDTOInterface) {
                    throw new \Exception('Input DTO class must implement InputDTOInterface.');
                }
            }

            $existingDTO->addError(InputDTOInterface::ERROR_DESERIALIZATION, $e->getMessage());
        }

        return $existingDTO;
    }
}