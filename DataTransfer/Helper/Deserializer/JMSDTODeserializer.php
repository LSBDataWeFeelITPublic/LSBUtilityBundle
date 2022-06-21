<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\Deserializer;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\SerializerInterface;
use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\DataTransfer\Model\Input\InputDTOInterface;
use LSB\UtilityBundle\Serializer\ObjectConstructor\ExistingObjectConstructor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class JMSDTODeserializer extends BaseDTODeserializer
{
    const MAX_NESTING_LEVEL = 4;

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

            $DTO =  $this->serializer->deserialize(
                $request->getContent(),
                $resource->getInputCreateDTOClass(),
                $request->getFormat($request->headers->get('Content-Type')),
                $context
            );

            $this->rewriteObjects($DTO);

            return $DTO;
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

    protected function rewriteObjects(InputDTOInterface $DTO, int $nestingLevel = 0): void
    {
        if ($DTO->getObject() && $nestingLevel <= self::MAX_NESTING_LEVEL) {
            //Assign collections
            $propertyAccessor = new PropertyAccessor();
            $reflectionClass = new \ReflectionClass($DTO);

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {

                if (!$reflectionProperty->getType()->isBuiltin()) {
                    try {
                        $reflectionClass = new \ReflectionClass($reflectionProperty->getType()->getName());
                    } catch (\Exception $e) {
                        $reflectionClass = null;
                    }
                } else {
                    $reflectionClass = null;
                }

                if ($reflectionClass && $reflectionClass->implementsInterface(InputDTOInterface::class)) {
                    if ($propertyAccessor->isReadable($DTO->getObject(), $reflectionProperty->getName())
                        && $propertyAccessor->isReadable($DTO, $reflectionProperty->getName())
                    ) {
                        $subDTO = $propertyAccessor->getValue($DTO, $reflectionProperty->getName());
                        if (!$subDTO instanceof InputDTOInterface) {
                            continue;
                        }

                        $subObject = $propertyAccessor->getValue($DTO->getObject(), $reflectionProperty->getName());

                        //Sprawdzamy zgodność identyfikatora
                        if ($propertyAccessor->isReadable($subDTO, 'uuid')
                            && $propertyAccessor->isReadable($subObject, 'uuid')
                            && $propertyAccessor->getValue($subDTO, 'uuid')
                            && $propertyAccessor->getValue($subObject, 'uuid')
                        ) {
                            $subDTO->setObject($subObject);
                            //Trzeba sprawdzić wszystkie wlasnosci danego input DTO pod kątem ich przepisania
                            $this->rewriteObjects($subDTO, $nestingLevel + 1);
                        }


                    }
                }

                if (!$reflectionProperty->getType() || !in_array($reflectionProperty->getType()->getName(), ['array', ArrayCollection::class])) {
                    continue;
                }

                if (in_array($reflectionProperty->getName(), ['errors'])) {
                    continue;
                }

                if ($reflectionProperty->getName() == 'translations') {
                    if ($propertyAccessor->isReadable($DTO, 'translations')) {
                        foreach ($propertyAccessor->getValue($DTO, 'translations') as $dtoTranslation) {
                            $objectTranslation = $DTO->getObject()->getTranslations()->get($dtoTranslation->locale);
                            $dtoTranslation->setObject($objectTranslation);
                        }
                    }
                } else {
                    if ($propertyAccessor->isReadable($DTO, $reflectionProperty->getName())) {
                        foreach ($propertyAccessor->getValue($DTO, $reflectionProperty->getName()) as $key => $dtoCollectionItem) {
                            $objectCollection = $propertyAccessor->getValue($DTO->getObject(), $reflectionProperty->getName());
                            foreach ($objectCollection as $objectCollectionItem) {
                                if ($propertyAccessor->isReadable($dtoCollectionItem, 'uuid')
                                    && $propertyAccessor->isReadable($objectCollectionItem, 'uuid')
                                    && $propertyAccessor->getValue($dtoCollectionItem, 'uuid') == $propertyAccessor->getValue($objectCollectionItem, 'uuid')
                                ) {
                                    $dtoCollectionItem->setObject($objectCollectionItem);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}