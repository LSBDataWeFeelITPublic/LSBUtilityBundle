<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

/**
 * Class BaseEntityVoter
 * @package LSB\UtilityBundle\Security
 */
abstract class BaseObjectVoter extends Voter implements ObjectVoterInterface
{
    const ACTION_GET = 'get';
    const ACTION_CGET = 'cget';
    const ACTION_PUT = 'put';
    const ACTION_POST = 'post';
    const ACTION_PATCH = 'patch';
    const ACTION_DELETE = 'delete';

    /**
     * @var Security
     */
    protected $security;

    /**
     * @return string[]
     */
    public function getSupportedActions(): array
    {
        return [
            self::ACTION_GET,
            self::ACTION_CGET,
            self::ACTION_PUT,
            self::ACTION_POST,
            self::ACTION_PATCH,
            self::ACTION_DELETE
        ];
    }

    /**
     * BaseEntityVoter constructor.
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @return object|null
     */
    protected function getRealSubject(?object $object, ?string $className = null): ?object
    {
        if ($object instanceof VoterSubjectInterface && ($className === null || $object->getSubject() instanceof $className)) {
            return $object->getSubject();
        } elseif ($className === null || $className !== null && $object instanceof $className) {
            return $object;
        }

        return null;
    }
}