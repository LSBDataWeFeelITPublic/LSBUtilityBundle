<?php

namespace LSB\UtilityBundle\Validator;

use LSB\UtilityBundle\Helper\ValueHelper;
use Money\Money;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

abstract class AbstractMoneyComparisonValidator extends ConstraintValidator
{
    const DEFAULT_CURRENCY = 'PLN';

    private ParameterBagInterface $parameterBag;

    private ?PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        ParameterBagInterface     $parameterBag,
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
        if (!$constraint instanceof AbstractMoneyComparison) {
            throw new UnexpectedTypeException($constraint, AbstractMoneyComparison::class);
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
        if (!$comparedValue instanceof Money) {
            $currency = $constraint->currency;

            if (!$currency && $constraint->currency) {
                if (null === $object = $this->context->getObject()) {
                    return;
                }

                $currency = $this->getPropertyAccessor()->isReadable($this->context->getObject(), $constraint->currencyPath) ? $this->getPropertyAccessor()->getValue($this->context->getObject(), $constraint->currencyPath) : null;
            }

            if (!$currency) {
                //get default value
                $currency = $this->parameterBag->has('money.currency.default') ? $this->parameterBag->get('money.currency.default') : self::DEFAULT_CURRENCY;
            }
            try {
                $comparedValue = ValueHelper::convertToMoney($comparedValue, $currency);
            } catch (\Throwable $e) {
                throw new ConstraintDefinitionException(sprintf('The compared value "%s" could not be converted to Money instance in the "%s" constraint.', $comparedValue, get_debug_type($constraint)));
            }
        }

        // Check value type
        if (!$value instanceof Money) {
            throw new ConstraintDefinitionException(sprintf('The value "%s" could not be converted to Money instance in the "%s" constraint.', $comparedValue, get_debug_type($constraint)));
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
        if ($value instanceof Money) {
            return ValueHelper::formatMoney($value);
        }

        return self::formatValue($value, $format);
    }

    /**
     * @param \Money\Money|null $value1
     * @param \Money\Money|null $value2
     * @return mixed
     */
    abstract protected function compareValues(?Money $value1, ?Money $value2);

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