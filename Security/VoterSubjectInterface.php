<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Security;


/**
 * Class BaseVoterSubject
 * @package LSB\UtilityBundle\Security
 */
interface VoterSubjectInterface
{
    /**
     * @return object
     */
    public function getSubject(): ?object;
}