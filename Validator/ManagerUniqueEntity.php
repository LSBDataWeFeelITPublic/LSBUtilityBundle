<?php

namespace LSB\UtilityBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for the Unique Entity validator. Use this constraint to verify the uniqueness of the data that is associated with an entity.
 *
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de> modified by LSB DATA
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class ManagerUniqueEntity extends Constraint
{
    public const NOT_UNIQUE_ERROR = '23bd9dbf-6b9b-41cd-a99e-4844bcf3077f';

    public string $manager;

    public string $message = 'This value is already used.';

    public $repositoryMethod = 'findBy';
    public $fields = [];
    public $entityFields = [];
    public $errorPath = null;
    public $ignoreNull = true;

    protected static $errorNames = [
        self::NOT_UNIQUE_ERROR => 'NOT_UNIQUE_ERROR',
    ];

    /**
     * Validates uniqueness of data for entity
     *
     * {@inheritdoc}
     *
     * @param array|string|null $fields the combination of fields in DTO object that must contain unique values or a set of options
     * @param array|string|null $entityFields the combination of fields in entity object that must contain unique values or a set of options
     * @param string $manager Manager class dedicated to the specific entity object
     * @param string $message Error message
     * @param string $repositoryMethod Method used in repository to fetch object
     * @param string $errorPath The name of the property that will return a validation error
     * @param bool $ignoreNull Ignore null values
     */
    public function __construct(
        array|string|null $fields,
        array|string|null $entityFields,
        string $manager,
        string $message = null,
        string $repositoryMethod = null,
        string $errorPath = null,
        bool $ignoreNull = null,
        array $groups = null,
        $payload = null,
        array $options = []
    ) {
        if (\is_array($fields) && \is_string(key($fields))) {
            $options = array_merge($fields, $options);
        } elseif (null !== $fields) {
            $options['fields'] = $fields;
        }

        if (\is_array($entityFields) && \is_string(key($entityFields))) {
            $options = array_merge($entityFields, $options);
        } elseif (null !== $entityFields) {
            $options['entityFields'] = $entityFields;
        }

        parent::__construct($options, $groups, $payload);

        $this->manager = $manager;
        $this->message = $message ?? $this->message;
        $this->repositoryMethod = $repositoryMethod ?? $this->repositoryMethod;
        $this->errorPath = $errorPath ?? $this->errorPath;
        $this->ignoreNull = $ignoreNull ?? $this->ignoreNull;
    }

    public function getRequiredOptions()
    {
        return ['fields', 'entityFields'];
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
