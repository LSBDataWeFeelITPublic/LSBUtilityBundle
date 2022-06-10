<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LSB\UtilityBundle\Validator;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\LogicException;

/**
 * Used for the comparison of values.
 *
 * @author Daniel Holmes <daniel@danielholmes.org>
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractValueComparison extends Constraint
{
    public $message;
    public $value;
    public $presision;
    public $propertyPath;
    public $precisionPath;
    public $unit;
    public $unitPath;

    /**
     * {@inheritdoc}
     *
     * @param mixed $value the value to compare or a set of options
     */
    public function __construct(
        $value = null,
        $propertyPath = null,
        $precision = null,
        $precisionPath = null,
        $unit = null,
        $unitPath = null,
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
        $this->precisionPath = $precisionPath ?? $this->precisionPath;
        $this->presision = $precision ?? $this->presision;
        $this->unit = $unit ?? $this->unitPath;
        $this->unitPath = $unitPath ?? $this->unitPath;

        if (null === $this->value && null === $this->propertyPath) {
            throw new ConstraintDefinitionException(sprintf('The "%s" constraint requires either the "value" or "propertyPath" option to be set.', static::class));
        }

        if (null !== $this->value && null !== $this->propertyPath) {
            throw new ConstraintDefinitionException(sprintf('The "%s" constraint requires only one of the "value" or "propertyPath" options to be set, not both.', static::class));
        }

        if (null !== $this->propertyPath && !class_exists(PropertyAccess::class)) {
            throw new LogicException(sprintf('The "%s" constraint requires the Symfony PropertyAccess component to use the "propertyPath" option.', static::class));
        }

        if (null !== $this->unitPath && !class_exists(PropertyAccess::class)) {
            throw new LogicException(sprintf('The "%s" constraint requires the Symfony PropertyAccess component to use the "unitPath" option.', static::class));
        }

        if (null !== $this->precisionPath && !class_exists(PropertyAccess::class)) {
            throw new LogicException(sprintf('The "%s" constraint requires the Symfony PropertyAccess component to use the "precisionPath" option.', static::class));
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
