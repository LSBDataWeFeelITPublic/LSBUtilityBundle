<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Security;

/**
 * Class BaseVoterSubject
 * @package LSB\UtilityBundle\Security
 */
abstract class BaseVoterSubject implements VoterSubjectInterface
{
    /**
     * @var object|null
     */
    protected ?object $subject;

    /**
     * BaseVoterSubject constructor.
     * @param object|null $subject
     */
    public function __construct(?object $subject = null)
    {
        $this->subject = $subject;
    }

    /**
     * @return object
     */
    public function getSubject(): ?object
    {
        return $this->subject;
    }
}