<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class AppHelper
{
    public function __construct(protected RequestStack $requestStack)
    {}

    public function getAppCode(?Request $request = null): ?string
    {
        if (!$request) {
            $request = $this->requestStack->getCurrentRequest();
        }

        $appCode = null;
        $controllerPath = $request->attributes->get('_controller');
        $path = explode('::', $controllerPath);

        if (!isset($path[0])) {
            return null;
        }

        $ownInterfaces = class_implements($path[0]);
        foreach ($ownInterfaces as $ownInterface) {
            if (defined("$ownInterface::CODE")) {
                $appCode = $ownInterface::CODE;
                break;
            }
        }

        return $appCode;
    }
}