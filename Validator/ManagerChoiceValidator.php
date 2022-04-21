<?php

namespace LSB\UtilityBundle\Validator;

use LSB\UtilityBundle\Manager\ManagerInterface;
use LSB\UtilityBundle\Service\ManagerContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ManagerChoiceValidator extends ConstraintValidator
{

    public function __construct(protected ManagerContainerInterface $managerContainer)
    {
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ManagerChoice) {
            throw new UnexpectedTypeException($constraint, ManagerChoice::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $value || '' === $value) {
            return;
        }

        $choices = [];

        if ($constraint->manager && $constraint->method) {
            $manager = $this->managerContainer->getByManagerClass($constraint->manager);

            if (!$manager instanceof ManagerInterface) {
                throw new ConstraintDefinitionException(sprintf('Object manager "%s" does not exist.', $constraint->manager));
            }

            if (method_exists($manager, $constraint->method)) {
                $method = $constraint->method;
                $choices = $manager->$method($value, $constraint->payload);
            } else {
                throw new ConstraintDefinitionException(sprintf('Method %s does not exist in %s.', $constraint->method, $constraint->manager));
            }
        }

        if ($constraint->multiple) {
            foreach ($value as $_value) {
                if (!\in_array($_value, $choices, true)) {
                    $this->context->buildViolation($constraint->multipleMessage)
                        ->setParameter('{{ value }}', $this->formatValue($_value))
                        ->setParameter('{{ choices }}', $this->formatValues($choices))
                        ->setCode(Choice::NO_SUCH_CHOICE_ERROR)
                        ->setInvalidValue($_value)
                        ->addViolation();

                    return;
                }
            }

            $count = \count($value);

            if (null !== $constraint->min && $count < $constraint->min) {
                $this->context->buildViolation($constraint->minMessage)
                    ->setParameter('{{ limit }}', $constraint->min)
                    ->setPlural((int)$constraint->min)
                    ->setCode(Choice::TOO_FEW_ERROR)
                    ->addViolation();

                return;
            }

            if (null !== $constraint->max && $count > $constraint->max) {
                $this->context->buildViolation($constraint->maxMessage)
                    ->setParameter('{{ limit }}', $constraint->max)
                    ->setPlural((int)$constraint->max)
                    ->setCode(Choice::TOO_MANY_ERROR)
                    ->addViolation();

                return;
            }
        } elseif (!\in_array($value, $choices, true)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setParameter('{{ choices }}', $this->formatValues($choices))
                ->setCode(Choice::NO_SUCH_CHOICE_ERROR)
                ->addViolation();
        }
    }

}