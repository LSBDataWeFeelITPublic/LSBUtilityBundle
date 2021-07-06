<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Exception\ObjectManager;

use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Class ValidationException
 * @package LSB\UtilityBundle\Exception\ObjectManager
 */
class ValidationException extends BaseObjectManagerException
{

    /**
     * @var ConstraintViolationList
     */
    private ConstraintViolationList $violations;

    /**
     * Constructor.
     *
     * @param ConstraintViolationList $violations Array with validation errors (violations).
     */
    public function __construct($violations)
    {
        $this->violations = $violations;


        parent::__construct("Validation failed\n ".$this->printMessage());
    }

    public function printMessage(): string
    {
        $messages = $this->getMessages();

        $string = '';

        foreach ($messages as $key => $messageData) {
            $string .= $key;
            $stringProperty = ': ';

            foreach ($messageData as $messageDatum) {
                $stringProperty .= $messageDatum . ' ';
            }

            $string .= $stringProperty . " \n";
        }

        return $string;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        $messages = [];

        /** @var ConstraintViolationList $violations */
        foreach ($this->violations as $key => $violation) {
                $propertyPath = $violation->getPropertyPath() ?? 'default';
                $messages[$propertyPath][] = $violation->getMessage();
        }

        return $messages;
    }

}