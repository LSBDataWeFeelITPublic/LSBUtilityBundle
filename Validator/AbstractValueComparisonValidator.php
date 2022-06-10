<?php

namespace LSB\UtilityBundle\Validator;

use LSB\UtilityBundle\Helper\ValueHelper;
use LSB\UtilityBundle\Value\Value;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

abstract class AbstractValueComparisonValidator extends ConstraintValidator
{
    private ParameterBagInterface $parameterBag;

    private ?PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        ParameterBagInterface $parameterBag,
        PropertyAccessorInterface $propertyAccessor = null
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->parameterBag = $parameterBag;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof AbstractValueComparison) {
            throw new UnexpectedTypeException($constraint, AbstractValueComparison::class);
        }

        if (null === $value) {
            return;
        }

        if ($path = $constraint->propertyPath) {
            if (null === $object = $this->context->getObject()) {
                return;
            }

            try {
                $comparedValue = $this->getPropertyAccessor()->getValue($object, $path);
            } catch (NoSuchPropertyException $e) {
                throw new ConstraintDefinitionException(sprintf('Invalid property path "%s" provided to "%s" constraint: ', $path, get_debug_type($constraint)) . $e->getMessage(), 0, $e);
            }
        } else {
            $comparedValue = $constraint->value;
        }

        // Check compared value type
        if (!$comparedValue instanceof Value) {

            $precision = $constraint->presision;

            if (!$precision && $constraint->propertyPath) {
                if (null === $object = $this->context->getObject()) {
                    return;
                }

                $precision = $this->getPropertyAccessor()->isReadable($this->context->getObject(), $constraint->propertyPath) ? $this->getPropertyAccessor()->getValue($this->context->getObject(), $constraint->propertyPath) : null;
            }

            if (!$precision) {
                //get default value
                $precision = $this->parameterBag->has('value.precision.default') ? $this->parameterBag->get('value.precision.default') : null;
            }

            $unit = $constraint->unit;

            if (!$unit && $constraint->unitPath) {
                if (null === $object = $this->context->getObject()) {
                    return;
                }

                $unit = $this->getPropertyAccessor()->isReadable($this->context->getObject(), $constraint->unitPath) ? $this->getPropertyAccessor()->getValue($this->context->getObject(), $constraint->unitPath) : null;
            }

            if (!$unit) {
                //get default value
                $unit = null;
            }

            try {
                $comparedValue = ValueHelper::convertToValue(
                    amount: $comparedValue,
                    unit: $unit,
                    precision: $precision
                );

            } catch (\Exception $e) {
                throw new ConstraintDefinitionException(sprintf('The compared value "%s" could not be converted to Value instance in the "%s" constraint.', $comparedValue, get_debug_type($constraint)));
            }
        }

        // Check value type
        if (!$value instanceof Value) {
                throw new ConstraintDefinitionException(sprintf('The value "%s" could not be converted to Value instance in the "%s" constraint.', $value, get_debug_type($constraint)));
        }

        if (!$this->compareValues($value, $comparedValue)) {
            $violationBuilder = $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value, self::OBJECT_TO_STRING | self::PRETTY_DATE))
                ->setParameter('{{ compared_value }}', $this->formatValue($comparedValue, self::OBJECT_TO_STRING | self::PRETTY_DATE))
                ->setParameter('{{ compared_value_type }}', $this->formatTypeOf($comparedValue))
                ->setCode($this->getErrorCode());

            if (null !== $path) {
                $violationBuilder->setParameter('{{ compared_value_path }}', $path);
            }

            $violationBuilder->addViolation();
        }
    }

    /**
     * @return \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private function getPropertyAccessor(): PropertyAccessorInterface
    {
        if (null === $this->propertyAccessor) {
            $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        return $this->propertyAccessor;
    }

    /**
     * @param $value
     * @param int $format
     * @return string|null
     */
    protected function formatValue($value, int $format = 0)
    {
        if ($value instanceof Value) {
            return ValueHelper::formatValue($value);
        }

        return self::formatValue($value, $format);
    }

    /**
     * @param \LSB\UtilityBundle\Value\Value|null $value1
     * @param \LSB\UtilityBundle\Value\Value|null $value2
     * @return mixed
     */
    abstract protected function compareValues(?Value $value1, ?Value $value2);

    /**
     * Returns the error code used if the comparison fails.
     *
     * @return string|null
     */
    protected function getErrorCode()
    {
        return null;
    }
}
