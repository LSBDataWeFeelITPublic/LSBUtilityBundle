<?php

namespace LSB\UtilityBundle\Validator;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\LogicException;

abstract class AbstractMoneyComparison extends Constraint
{
    public $message;
    public $value;
    public $propertyPath;
    public $currency;
    public $currencyPath;

    /**
     * {@inheritdoc}
     *
     * @param mixed $value the value to compare or a set of options
     */
    public function __construct(
        $value = null,
        $propertyPath = null,
        $currency = null,
        $currencyPath = null,
        string $message = null,
        array $groups = null,
        $payload = null,
        array $options = []
    ) {
        if (\is_array($value)) {
            $options = array_merge($value, $options);
        } elseif (null !== $value) {
            $options['value'] = $value;
        }

        parent::__construct($options, $groups, $payload);

        $this->message = $message ?? $this->message;
        $this->propertyPath = $propertyPath ?? $this->propertyPath;
        $this->currency = $currency ?? $this->currency;
        $this->currencyPath = $currencyPath ?? $this->currencyPath;

        if (null === $this->value && null === $this->propertyPath) {
            throw new ConstraintDefinitionException(sprintf('The "%s" constraint requires either the "value" or "propertyPath" option to be set.', static::class));
        }

        if (null !== $this->value && null !== $this->propertyPath) {
            throw new ConstraintDefinitionException(sprintf('The "%s" constraint requires only one of the "value" or "propertyPath" options to be set, not both.', static::class));
        }

        if (null !== $this->propertyPath && !class_exists(PropertyAccess::class)) {
            throw new LogicException(sprintf('The "%s" constraint requires the Symfony PropertyAccess component to use the "propertyPath" option.', static::class));
        }

        if (null !== $this->currencyPath && !class_exists(PropertyAccess::class)) {
            throw new LogicException(sprintf('The "%s" constraint requires the Symfony PropertyAccess component to use the "currencyPath" option.', static::class));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption()
    {
        return 'value';
    }
}
