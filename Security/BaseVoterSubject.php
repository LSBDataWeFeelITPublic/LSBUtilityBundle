<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Security;

use LSB\UtilityBundle\Application\AppCodeTrait;

/**
 * Class BaseVoterSubject
 * @package LSB\UtilityBundle\Security
 */
abstract class BaseVoterSubject implements VoterSubjectInterface
{
    use AppCodeTrait;

    /**
     * @var object|null
     */
    protected ?object $subject;

    /**
     * BaseVoterSubject constructor.
     * @param object|null $subject
     */
    public function __construct(?object $subject = null, ?string $appCode = null)
    {
        $this->subject = $subject;
        $this->appCode = $appCode;
    }

    /**
     * @return object
     */
    public function getSubject(): ?object
    {
        return $this->subject;
    }
}