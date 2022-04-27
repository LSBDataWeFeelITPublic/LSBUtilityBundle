<?php

namespace LSB\UtilityBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * With this constraint, you will be able to use the manager service to build a choice list for a specific property validation.
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
#[\Attribute]
class ManagerChoice extends Constraint
{
    public string $manager;

    public string $method;

    public ?int $min = null;

    public ?int $max = null;

    public bool $multiple = false;

    public string $message = 'The value you selected is not a valid choice.';
    public string $multipleMessage = 'One or more of the given values is invalid.';
    public string $minMessage = 'You must select at least {{ limit }} choice.|You must select at least {{ limit }} choices.';
    public string $maxMessage = 'You must select at most {{ limit }} choice.|You must select at most {{ limit }} choices.';

    /**
     * {@inheritDoc}
     *
     * @param string $manager Manager class
     * @param string $method Method name in manager class
     * @param bool $multiple Multiple choices
     * @param int|null $min Min count
     * @param int|null $max Max count
     */
    public function __construct(
        string $manager,
        string $method,
        bool   $multiple = false,
        ?int   $min = null,
        ?int   $max = null,
               $options = null,
        array  $groups = null,
               $payload = null
    ) {
        parent::__construct($options, $groups, $payload);

        $this->manager = $manager;
        $this->method = $method;
        $this->min = $min;
        $this->max = $max;
        $this->multiple = $multiple;
    }

}