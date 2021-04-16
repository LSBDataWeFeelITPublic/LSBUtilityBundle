<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Security;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Interface EntityVoterInterface
 * @package LSB\UtilityBundle\Security
 */
interface ObjectVoterInterface extends VoterInterface
{
    /**
     * @return string[]
     */
    public function getSupportedActions(): array;
}