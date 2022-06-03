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
use Money\Money;

/**
 * Class MoneyHandler
 * @package App\Handler
 */
class MoneyHandler implements SubscribingHandlerInterface
{
    const KEY_CURRENCY = 'currency';
    const KEY_AMOUNT = 'amount';

    /**
     * @var bool
     */
    private $initializeExcluded;

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
            Money::class
        ];

        foreach ($collectionTypes as $type) {
            foreach ($formats as $format) {
                $methods[] = [
                    'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                    'type' => $type,
                    'format' => $format,
                    'method' => 'serializeMoney',
                ];

                $methods[] = [
                    'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                    'type' => $type,
                    'format' => $format,
                    'method' => 'deserializeMoney',
                ];
            }
        }

        return $methods;
    }

    /**
     * @return array|\ArrayObject
     */
    public function serializeMoney(SerializationVisitorInterface $visitor, Money $money, array $type, SerializationContext $context)
    {

        return $visitor->visitArray([
            self::KEY_AMOUNT => $money->getAmount(),
            self::KEY_CURRENCY => $money->getCurrency()->getCode()
        ], $type);
    }

    /**
     * @param mixed $data
     * @throws \Exception
     */
    public function deserializeMoney(DeserializationVisitorInterface $visitor, $data, array $type, DeserializationContext $context): ?Money
    {
        if (!is_array($data) && $data !== null) {
            throw new RuntimeException("Null or array is required for Money type.");
        }

        if ($data === null) {
            return null;
        }

        if (!array_key_exists(self::KEY_CURRENCY, $data)) {
            throw new RuntimeException("Currency is required for Money.");
        }

        if (!array_key_exists(self::KEY_AMOUNT, $data)) {
            throw new RuntimeException("Amount is required for Money.");
        }

        if (!isset($data[self::KEY_AMOUNT]) || !isset($data[self::KEY_CURRENCY])) {
            return null;
        }

        $currency = (string)$data[self::KEY_CURRENCY];
        $amount = (string)$data[self::KEY_AMOUNT];

        return Money::$currency($amount);
    }
}