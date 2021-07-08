<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Security;

use LSB\UtilityBundle\Application\AppCodeTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

/**
 * Class BaseEntityVoter
 * @package LSB\UtilityBundle\Security
 */
abstract class BaseObjectVoter extends Voter implements ObjectVoterInterface
{
    use AppCodeTrait;

    const SUBJECT_CLASS = null;
    const VOTER_SUBJECT_CLASS = null;

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
     * @var string|null
     */
    protected ?string $entityClass = null;

    /**
     * @var string|null
     */
    protected ?string $voterSubjectClass = null;

    /**
     * @var array
     */
    protected array $supportedAppCodes = [];

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
     * @return array
     */
    public function getSupportedAppCodes(): array
    {
        return $this->supportedAppCodes;
    }

    /**
     * @param $appCode
     */
    public function setSupportedAppCodes($appCode): void
    {
        if (is_array($appCode)) {
            $this->supportedAppCodes = $appCode;
        } else {
            $this->supportedAppCodes = [(string)$appCode];
        }
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
     * @param $action
     * @return bool
     */
    protected function isActionSupported($action): bool
    {
        return in_array($action, $this->getSupportedActions());
    }

    /**
     * @param string|null $appCode
     * @return bool
     */
    protected function isAppCodeSupported($subject): bool
    {
        $appCode = null;

        if ($subject instanceof VoterSubjectInterface) {
            $appCode = $subject->getAppCode();
        } elseif ($subject) {
            $appCode = (string) $subject;
        }

        if (!$appCode && !count($this->getSupportedAppCodes())) {
            return true;
        }

        return $appCode ? in_array($appCode, $this->getSupportedAppCodes()) : false;
    }

    /**
     * @param mixed $object
     * @param string|null $className
     * @return mixed
     */
    protected function getRealSubject(mixed $object, ?string $className = null): mixed
    {
        if ($object instanceof VoterSubjectInterface && ($className === null || $object->getSubject() instanceof $className)) {
            return $object->getSubject();
        } elseif ($className === null || $className !== null && $object instanceof $className) {
            return $object;
        }

        return null;
    }

    /**
     * @param object|null $object
     * @return string|null
     */
    protected function getSubjectAppCode(?object $object): ?string
    {
        if ($object instanceof VoterSubjectInterface) {
            return $object->getAppCode();
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }

    /**
     * @param string|null $entityClass
     * @return BaseObjectVoter
     */
    public function setEntityClass(?string $entityClass): BaseObjectVoter
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getVoterSubjectClass(): ?string
    {
        return $this->voterSubjectClass;
    }

    /**
     * @param string|null $voterSubjectClass
     * @return BaseObjectVoter
     */
    public function setVoterSubjectClass(?string $voterSubjectClass): BaseObjectVoter
    {
        $this->voterSubjectClass = $voterSubjectClass;
        return $this;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        $realSubject = $this->getRealSubject($subject);
        $class = static::SUBJECT_CLASS;
        $voterSubjectClass = static::VOTER_SUBJECT_CLASS;

        return $this->isActionSupported($attribute)
            && $this->isAppCodeSupported($subject)
            && ($realSubject instanceof $class || $subject instanceof $voterSubjectClass);
    }
}