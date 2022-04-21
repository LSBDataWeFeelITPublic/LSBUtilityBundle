<?php

namespace LSB\UtilityBundle\DTO\Resource;

use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\Manager\ManagerInterface;
use LSB\UtilityBundle\Service\ManagerContainerInterface;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;

class ResourceHelper
{
    public function __construct(protected ManagerContainerInterface $managerContainer){}

    /**
     * @param Request $request
     * @return Resource|null
     * @throws \Exception
     */
    public function __invoke(Request $request): ?Resource
    {
        return $this->fetchResource($request);
    }

    /**
     * @param Request $request
     * @return Resource|null
     * @throws \Exception
     */
    public function fetchResource(Request $request): ?Resource
    {
        if (!$request->attributes->has('_controller')) {
            return null;
        }

        $manager = null;
        $methodData = explode("::", $request->attributes->get('_controller'));
        /**
         * @var Resource|null $controllerResource
         */
        $controllerResource = null;

        if (count($methodData) < 2) {
            return null;
        }

        //Zczytanie atrybutów z kontrolera/klasy

        $controllerClass = $methodData[0];

        $controllerResource = $this->getClassResource($controllerClass);

        if (!$controllerResource) {
            return null;
        }

        $manager = $this->managerContainer->getByManagerClass($controllerResource->getManagerClass());

        $entityClass = null;

        if ($manager instanceof ManagerInterface && $manager->getResourceEntityClass()) {
            $entityClass = $manager->getResourceEntityClass();
        }

        if (!$entityClass) {
            //TODO Prepare solution in case of missing manager and null entity class.
            return null;
        }

        $this->updateResourceConfiguration($controllerResource, $manager);

        //We've got entity class
        //We fetch entity attributes.
        $entityResource = $this->getClassResource($entityClass);

        //Atrybuty encji, mając atrybut encji, mamy dostęp do obiektów DTO
        //Do rozwazenia budowa obiketu Resource na podstawie poszczegolnych atrybutow Resource

        //We have entity resource
        //$entityResource

        //We have controller resource
        //$controllerResource

        //Concept with method Resource
        if ($entityResource && $controllerResource) {
            $mergedClassResource = $this->mergeResource($entityResource, $controllerResource);
        } elseif ($entityResource) {
            $mergedClassResource = $entityResource;
        } elseif ($controllerResource) {
            $mergedClassResource = $controllerResource;
        } else {
            $mergedClassResource = null;
        }

        $reflectionMethod = new ReflectionMethod($methodData[0], $methodData[1]);
        $methodAttributes = $reflectionMethod->getAttributes(Resource::class);

        /**
         * @var Resource|null $methodResource
         */
        $methodResource = null;
        foreach ($methodAttributes as $methodAttribute) {
            if ($methodAttribute->getName() !== Resource::class) {
                continue;
            }

            $methodResource = $methodAttribute->newInstance();
        }

        if ($mergedClassResource && $methodResource) {
            $mergedResource = $this->mergeResource($mergedClassResource, $methodResource);
        } elseif ($methodResource) {
            $mergedResource = $methodResource;
        } elseif ($mergedClassResource) {
            $mergedResource = $mergedClassResource;
        } else {
            //No Resource object could be fetched.
            return null;
        }

        return $mergedResource;
    }

    /**
     * @param string $class
     * @return \LSB\UtilityBundle\Attribute\Resource|null
     */
    public function getClassResource(string $class): ?Resource
    {
        try {
            $entityReflectionClass = new ReflectionClass($class);
        } catch (\Exception $e) {
            return null;
        }

        $classAttributes = $entityReflectionClass->getAttributes(Resource::class);

        /**
         * @var Resource|null $resource
         */
        $resource = null;
        foreach ($classAttributes as $entityAttribute) {
            if ($entityAttribute->getName() !== Resource::class) {
                continue;
            }
            $resource = $entityAttribute->newInstance();
        }

        return $resource;
    }

    /**
     * @param Resource $LSresource
     * @param Resource $MSresource
     * @return Resource
     */
    public function mergeResource(Resource $LSresource, Resource $MSresource): Resource
    {

        return new Resource(
            $MSresource->getEntityClass() ?? $LSresource->getEntityClass(),
            $MSresource->getManagerClass() ?? $LSresource->getManagerClass(),
            $MSresource->getInputCreateDTOClass() ?? $LSresource->getInputCreateDTOClass(),
            $MSresource->getInputUpdateDTOClass() ?? $LSresource->getInputUpdateDTOClass(),
            $MSresource->getOutputDTOClass() ?? $LSresource->getOutputDTOClass(),
            $MSresource->getDeserializationType() ?? $LSresource->getDeserializationType(),
            $MSresource->getCollectionItemDeserializationType() ?? $LSresource->getCollectionItemDeserializationType(),
            $MSresource->getIsDisabled() ?? $LSresource->getIsDisabled(),
            $MSresource->getIsCollection() ?? $LSresource->getIsCollection(),
            $MSresource->getCollectionOutputDTOClass() ?? $LSresource->getCollectionOutputDTOClass(),
            $MSresource->getCollectionItemOutputDTOClass() ?? $LSresource->getCollectionItemOutputDTOClass()
        );
    }

    /**
     * @param Resource $resource
     * @param \LSB\UtilityBundle\Manager\ManagerInterface|null $manager
     * @return Resource
     */
    public function updateResourceConfiguration(Resource $resource, ?ManagerInterface $manager = null): Resource
    {
        if (!$resource->getEntityClass() && $manager) {
            $resource->setEntityClass($manager->getResourceEntityClass());
        }

        if (!$resource->getInputUpdateDTOClass(false)) {
            $resource->setInputUpdateDTOClass($resource->getInputCreateDTOClass());
        }

        if (!$resource->getCollectionItemOutputDTOClass()) {
            $resource->setCollectionItemOutputDTOClass($resource->getOutputDTOClass());
        }

        return $resource;
    }
}