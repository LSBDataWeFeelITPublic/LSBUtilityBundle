<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\CRUD\Route;

use LSB\UtilityBundle\DataTransfer\Request\RequestAttributes;
use LSB\UtilityBundle\Interfaces\UuidInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NewResourceGetRouteGenerator extends BaseRouteGenerator
{
    public function getPath(Request $request, ?int $referencePath = null): ?string
    {
        if (!$referencePath) {
            $referencePath = UrlGeneratorInterface::ABSOLUTE_PATH;
        }

        $requestData = RequestAttributes::getOrCreateRequestData($request);

        $propertyAccessor = new PropertyAccessor();

        if (!$requestData->getObject()
            && !$requestData->getObject() instanceof UuidInterface
            && !$propertyAccessor->isReadable($requestData->getObject(), 'uuid')
        ) {
            return null;
        }

        if ($request->getMethod() !== Request::METHOD_POST && $request->getMethod() !== Request::METHOD_PUT) {
            return null;
        }

        //Fetch routing
        if (!$requestData->getResource()->getIsCRUD()) {
            return null;
        }

        $controllerData = explode("::", $request->attributes->get('_controller'));
        $controllerClass = $controllerData[0];

        if (!$controllerClass) {
            return null;
        }

        $getActionDefault = "{$controllerClass}::getAction";

        $getRouteName = null;

        /**
         * @var \Symfony\Component\Routing\Route $route
         */
        foreach ($this->router->getRouteCollection() as $routeName => $route) {

            if ($route->getDefault('_controller') === $getActionDefault) {
                $getRouteName = $routeName;
                break;
            }
        }

        if (!$getRouteName) {
            return null;
        }

        return $this->router->generate(
            name: $getRouteName,
            parameters: ['uuid' => $propertyAccessor->getValue($requestData->getObject(), 'uuid')],
            referenceType: $referencePath
        );
    }
}