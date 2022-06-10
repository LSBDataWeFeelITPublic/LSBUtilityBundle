<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Serializer\Handler;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use LSB\UtilityBundle\Helper\ValueHelper;
use LSB\UtilityBundle\Value\Value;
use LSB\UtilityBundle\Serializer\Handler\Helper\HandlerHelper;


class ValueHandler implements SubscribingHandlerInterface
{
    const KEY_PRECISION = 'precision';
    const KEY_AMOUNT = 'amount';
    const KEY_UNIT = 'unit';

    private ?bool $initializeExcluded = null;

    public function __construct(bool $initializeExcluded = true)
    {
        $this->initializeExcluded = $initializeExcluded;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        $methods = [];
        $formats = ['json', 'xml', 'yml'];
        $collectionTypes = [
            Value::class
        ];

        foreach ($collectionTypes as $type) {
            foreach ($formats as $format) {
                $methods[] = [
                    'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                    'type' => $type,
                    'format' => $format,
                    'method' => 'serializeValue',
                ];

                $methods[] = [
                    'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                    'type' => $type,
                    'format' => $format,
                    'method' => 'deserializeValue',
                ];
            }
        }

        return $methods;
    }

    /**
     * @return array|\ArrayObject
     */
    public function serializeValue(SerializationVisitorInterface $visitor, Value $value, array $type, SerializationContext $context)
    {

        return $visitor->visitArray([
            self::KEY_AMOUNT => $value->getAmount(),
            self::KEY_UNIT => $value->getUnit(),
            self::KEY_PRECISION => $value->getPrecision()
        ], $type);
    }

    /**
     * @param mixed $data
     */
    public function deserializeValue(DeserializationVisitorInterface $visitor, $data, array $type, DeserializationContext $context): ?Value
   {
        $currentPathString = HandlerHelper::getCurrentPath($context);

        if (!is_array($data) && $data !== null) {
            throw new RuntimeException("Null or array is required for Value type.");
        }

        if ($data === null) {
            return null;
        }

        if (!array_key_exists('amount', $data)) {
            throw new RuntimeException("{$currentPathString}: key 'amount' is required for Value.");
        }

        if (!isset($data[self::KEY_AMOUNT])) {
            return null;
        }

        return ValueHelper::intToValue(
            (int) $data[self::KEY_AMOUNT],
            isset($data[self::KEY_UNIT]) ? (string)$data[self::KEY_UNIT] : null,
            isset($data[self::KEY_PRECISION]) ? (int)$data[self::KEY_PRECISION] : null
        );
    }
}