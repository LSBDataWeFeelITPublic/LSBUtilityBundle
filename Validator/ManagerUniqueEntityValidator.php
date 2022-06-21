<?php

namespace LSB\UtilityBundle\Validator;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use LSB\UtilityBundle\Manager\ManagerInterface;
use LSB\UtilityBundle\Service\ManagerContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @author Benjamin Eberlei <kontakt@beberlei.de> modified by Krzysztof Mazur
 */
class ManagerUniqueEntityValidator extends ConstraintValidator
{

    public function __construct(
        protected ManagerContainerInterface $managerContainer
    ) {
    }

    /**
     * @param \LSB\UtilityBundle\DataTransfer\Model\DTOInterface $object
     * @param \Symfony\Component\Validator\Constraint $constraint
     * @throws \Exception
     */
    public function validate($object, Constraint $constraint)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $manager = null;

        if (!$constraint instanceof ManagerUniqueEntity) {
            throw new UnexpectedTypeException($constraint, ManagerUniqueEntity::class);
        }

        if (!\is_array($constraint->fields) && !\is_string($constraint->fields)) {
            throw new UnexpectedTypeException($constraint->fields, 'array');
        }

        if (null !== $constraint->errorPath && !\is_string($constraint->errorPath)) {
            throw new UnexpectedTypeException($constraint->errorPath, 'string or null');
        }

        $fields = (array)$constraint->fields;
        $entityFields = (array)$constraint->entityFields;

        if (0 === \count($fields)) {
            throw new ConstraintDefinitionException('At least one field has to be specified.');
        }

        if (null === $object) {
            return;
        }

        if (!\is_object($object)) {
            throw new UnexpectedValueException($object, 'object');
        }

        if ($constraint->manager) {
            $manager = $this->managerContainer->getByManagerClass($constraint->manager);
        }

        if (!$manager instanceof ManagerInterface) {
            throw new ConstraintDefinitionException(sprintf('Object manager "%s" does not exist.', $constraint->manager));
        }

        $em = $manager->getObjectManager()->getEntityManager();

        $entityClass = $em->getClassMetadata($manager->getResourceEntityClass());

        $criteria = [];
        $hasNullValue = false;

        if (count($fields) !== count($entityFields)) {
            throw new ConstraintDefinitionException(sprintf('Fields and entityFields count must be equal.'));
        }

        foreach ($fields as $key => $field) {

            $fieldName = $field;
            $entityFieldName = $entityFields[$key];


            if (!$entityClass->hasField($entityFieldName) && !$entityClass->hasAssociation($entityFieldName)) {
                throw new ConstraintDefinitionException(sprintf('The field "%s" is not mapped by Doctrine, so it cannot be validated for uniqueness.', $entityFieldName));
            }

            $entityFieldValue = $propertyAccessor->getValue($object, $fieldName);

            if (null === $entityFieldValue) {
                $hasNullValue = true;
            }

            if ($constraint->ignoreNull && null === $entityFieldValue) {
                continue;
            }

            $criteria[$entityFieldName] = $entityFieldValue;

            if (null !== $criteria[$entityFieldName] && $entityClass->hasAssociation($entityFieldName)) {
                /* Ensure the Proxy is initialized before using reflection to
                 * read its identifiers. This is necessary because the wrapped
                 * getter methods in the Proxy are being bypassed.
                 */
                $em->initializeObject($criteria[$entityFieldName]);
            }
        }

        // validation doesn't fail if one of the fields is null and if null values should be ignored
        if ($hasNullValue && $constraint->ignoreNull) {
            return;
        }

        // skip validation if there are no criteria (this can happen when the
        // "ignoreNull" option is enabled and fields to be checked are null
        if (empty($criteria)) {
            return;
        }


        $repository = $manager->getRepository();

        $arguments = [$criteria];

        /* If the default repository method is used, it is always enough to retrieve at most two entities because:
         * - No entity returned, the current entity is definitely unique.
         * - More than one entity returned, the current entity cannot be unique.
         * - One entity returned the uniqueness depends on the current entity.
         */
        if ('findBy' === $constraint->repositoryMethod) {
            $arguments = [$criteria, null, 2];
        }

        $result = $repository->{$constraint->repositoryMethod}(...$arguments);

        if ($result instanceof \IteratorAggregate) {
            $result = $result->getIterator();
        }

        /* If the result is a MongoCursor, it must be advanced to the first
         * element. Rewinding should have no ill effect if $result is another
         * iterator implementation.
         */
        if ($result instanceof \Iterator) {
            $result->rewind();
            if ($result instanceof \Countable && 1 < \count($result)) {
                $result = [$result->current(), $result->current()];
            } else {
                $result = $result->valid() && null !== $result->current() ? [$result->current()] : [];
            }
        } elseif (\is_array($result)) {
            reset($result);
        } else {
            $result = null === $result ? [] : [$result];
        }

        /* If no entity matched the query criteria or a single entity matched,
         * which is the same as the entity being validated, the criteria is
         * unique.
         */

        if (!$result || (1 === \count($result) && (current($result)) === $object?->getObject())) {
            return;
        }

        $errorPath = $constraint->errorPath ?? $fields[0];
        $invalidValue = $criteria[$errorPath] ?? $criteria[$fields[0]];

        $this->context->buildViolation($constraint->message)
            ->atPath($errorPath)
            ->setParameter('{{ value }}', $this->formatWithIdentifiers($em, $entityClass, $invalidValue))
            ->setInvalidValue($invalidValue)
            ->setCode(ManagerUniqueEntity::NOT_UNIQUE_ERROR)
            ->setCause($result)
            ->addViolation();
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $em
     * @param \Doctrine\Persistence\Mapping\ClassMetadata $class
     * @param $value
     * @return string
     */
    private function formatWithIdentifiers(ObjectManager $em, ClassMetadata $class, $value): string
    {
        if (!\is_object($value) || $value instanceof \DateTimeInterface) {
            return $this->formatValue($value, self::PRETTY_DATE);
        }

        if (method_exists($value, '__toString')) {
            return (string)$value;
        }

        if ($class->getName() !== $idClass = \get_class($value)) {
            // non unique value might be a composite PK that consists of other entity objects
            if ($em->getMetadataFactory()->hasMetadataFor($idClass)) {
                $identifiers = $em->getClassMetadata($idClass)->getIdentifierValues($value);
            } else {
                // this case might happen if the non unique column has a custom doctrine type and its value is an object
                // in which case we cannot get any identifiers for it
                $identifiers = [];
            }
        } else {
            $identifiers = $class->getIdentifierValues($value);
        }

        if (!$identifiers) {
            return sprintf('object("%s")', $idClass);
        }

        array_walk($identifiers, function (&$id, $field) {
            if (!\is_object($id) || $id instanceof \DateTimeInterface) {
                $idAsString = $this->formatValue($id, self::PRETTY_DATE);
            } else {
                $idAsString = sprintf('object("%s")', \get_class($id));
            }

            $id = sprintf('%s => %s', $field, $idAsString);
        });

        return sprintf('object("%s") identified by (%s)', $idClass, implode(', ', $identifiers));
    }
}