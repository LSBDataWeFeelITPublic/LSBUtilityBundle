<?php

namespace LSB\UtilityBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Runs a callback method on the specified manager class for validation of whole DTO object.
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ManagerCallback extends Constraint
{
    public ?string $method = null;

    public ?string $manager = null;

    /**
     * {@inheritdoc}
     *
     * @param string|null $manager Manager class dedicated to the specific entity object
     * @param string|null $method Method name in manager class
     */
    public function __construct(
        ?string $manager = null,
        ?string $method = null,
        array   $groups = null,
                $payload = null,
        array   $options = []
    ) {
        $manager = $manager ?? $options['manager'] ?? null;
        $method = $method ?? $options['method'] ?? null;

        $this->manager = $manager;
        $this->method = $method;

        if (!$this->manager) {
            throw new InvalidArgumentException('Manager class must be set.');
        }

        if (!$this->method) {
            throw new InvalidArgumentException('Validation method must be set.');
        }

        parent::__construct($options, $groups, $payload);
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
