<?php

declare(strict_types=1);

namespace LSB\UtilityBundle\Serializer\ObjectConstructor;

use JMS\Serializer\Construction\DoctrineObjectConstructor;
use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;

class ExistingObjectConstructor implements ObjectConstructorInterface
{
    public const ATTRIBUTE_TARGET = 'deserialization-constructor-target';

    public const ATTRIBUTE_TARGET_USED = 'deserialization-constructor-target-used';

    private ObjectConstructorInterface $fallbackConstructor;

    private ObjectConstructorInterface $doctrineObjectConstructor;

    private bool $isUsedExistingObject = false;

    /**
     * @param ObjectConstructorInterface $fallbackConstructor
     * @param \JMS\Serializer\Construction\DoctrineObjectConstructor $doctrineObjectConstructor
     */
    public function __construct(ObjectConstructorInterface $fallbackConstructor, DoctrineObjectConstructor $doctrineObjectConstructor)
    {
        $this->fallbackConstructor = $fallbackConstructor;
        $this->doctrineObjectConstructor = $doctrineObjectConstructor;
    }

    /**
     * @param DeserializationVisitorInterface $visitor
     * @param ClassMetadata $metadata
     * @param mixed $data
     * @param array $type
     * @param DeserializationContext $context
     * @return object|null
     */
    public function construct(DeserializationVisitorInterface $visitor, ClassMetadata $metadata, $data, array $type, DeserializationContext $context): ?object
    {

        if ($context->hasAttribute(self::ATTRIBUTE_TARGET) && !$this->isUsedExistingObject) {
            $baseObject = $context->getAttribute(self::ATTRIBUTE_TARGET);
            $this->isUsedExistingObject = true;
            return $baseObject;
        }

        //dump($this->doctrineObjectConstructor->construct($visitor, $metadata, $data, $type, $context));

        return $this->doctrineObjectConstructor->construct($visitor, $metadata, $data, $type, $context);

        return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
    }
}