<?php

namespace LSB\UtilityBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for the Unique Entity validator.
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
     * {@inheritdoc}
     *
     * @param array|string $fields the combination of fields that must contain unique values or a set of options
     */
    public function __construct(
        $fields,
        $entityFields,
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
