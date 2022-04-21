<?php

namespace LSB\UtilityBundle\Validator;

use LSB\UtilityBundle\Manager\ManagerInterface;
use LSB\UtilityBundle\Service\ManagerContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ManagerCallbackValidator extends ConstraintValidator
{
    public function __construct(protected ManagerContainerInterface $managerContainer)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validate($object, Constraint $constraint)
    {
        if (!$constraint instanceof ManagerCallback) {
            throw new UnexpectedTypeException($constraint, ManagerCallback::class);
        }

        if (!is_string($constraint->manager) && $constraint->manager) {
            throw new ConstraintDefinitionException('Manager FQCN is missing.');
        }

        $manager = $this->managerContainer->getByManagerClass($constraint->manager);

        if (!$manager instanceof ManagerInterface) {
            throw new ConstraintDefinitionException(sprintf('Object manager "%s" does not exist.', $constraint->manager));
        }

        if (!is_string($constraint->method) && $constraint->method) {
            throw new ConstraintDefinitionException('Method name is missing.');
        }

        $method = $constraint->method;

        if (!method_exists($manager, $method)) {
            throw new ConstraintDefinitionException(sprintf('Method %s does not exist in %s.', $constraint->method, $constraint->manager));
        }


        $manager->$method($object, $this->context, $constraint->payload);

    }
}
