<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\Authorization;

use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\DataTransfer\Helper\App\AppHelper;
use LSB\UtilityBundle\Service\ManagerContainer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AuthorizationHelper
{
    public function __construct(
        protected AuthorizationCheckerInterface $authorizationChecker,
        protected ManagerContainer              $managerContainer,
        protected AppHelper                     $appHelper
    ) {

    }

    public function isGranted(Resource $resource, Request $request, string $action, $subject = null): bool
    {
        if ($resource->getManagerClass()) {
            $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());
        } else {
            $manager = null;
        }

        return $this->authorizationChecker->isGranted($action, $manager?->getVoterSubject($subject, $this->appHelper->getAppCode($request)));
    }

    public function isSubjectGranted(string $managerClass, Request $request, string $action, $subject = null): bool
    {
        $manager = $this->managerContainer->getByManagerClass($managerClass);
        return $this->authorizationChecker->isGranted($action, $manager->getVoterSubject($subject, $this->appHelper->getAppCode($request)));
    }
}