<?php

namespace LSB\UtilityBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
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
     * @param array|string|callable $method The callback or a set of options
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
