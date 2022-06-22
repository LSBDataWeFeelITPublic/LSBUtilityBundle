<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\Resource;

use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\DataTransfer\Model\Output\BaseCollectionOutputDTO;
use LSB\UtilityBundle\Manager\ManagerInterface;
use LSB\UtilityBundle\Service\ManagerContainerInterface;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;

class ResourceHelper
{
    public function __construct(protected ManagerContainerInterface $managerContainer)
    {
    }

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
        $entityResource = null;

        if (count($methodData) < 2) {
            return null;
        }

        //Zczytanie atrybutów z kontrolera/klasy

        $controllerClass = $methodData[0];

        $controllerResource = $this->getClassResource($controllerClass);

        if (!$controllerResource) {
            return null;
        }

        if ($controllerResource->getManagerClass()) {
            $manager = $this->managerContainer->getByManagerClass($controllerResource->getManagerClass());
        }


        $entityClass = null;

        if ($manager instanceof ManagerInterface && $manager->getResourceEntityClass()) {
            $entityClass = $manager->getResourceEntityClass();
        }

//        if (!$entityClass) {
//            //TODO Prepare solution in case of missing manager and null entity class.
//            return null;
//        }

        if ($manager) {
            $this->updateResourceConfigurationWithDefaults($controllerResource, $manager);
        }


        //We've got entity class
        //We fetch entity attributes.
        if ($entityClass) {
            $entityResource = $this->getClassResource($entityClass);
        }

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
            objectClass: $MSresource->getObjectClass() ?? $LSresource->getObjectClass(),
            managerClass: $MSresource->getManagerClass() ?? $LSresource->getManagerClass(),
            inputDTOClass: $MSresource->getInputDTOClass() ?? $LSresource->getInputDTOClass(),
            inputCreateDTOClass: $MSresource->getInputCreateDTOClass() ?? $LSresource->getInputCreateDTOClass(),
            inputUpdateDTOClass: $MSresource->getInputUpdateDTOClass() ?? $LSresource->getInputUpdateDTOClass(),
            outputDTOClass: $MSresource->getOutputDTOClass() ?? $LSresource->getOutputDTOClass(),
            serializationType: $MSresource->getSerializationType() ?? $LSresource->getSerializationType(),
            collectionItemSerializationType: $MSresource->getCollectionItemSerializationType() ?? $LSresource->getCollectionItemSerializationType(),
            isDisabled: $MSresource->getIsDisabled() ?? $LSresource->getIsDisabled(),
            isCollection: $MSresource->getIsCollection() ?? $LSresource->getIsCollection(),
            collectionOutputDTOClass: $MSresource->getCollectionOutputDTOClass() ?? $LSresource->getCollectionOutputDTOClass(),
            collectionItemInputDTOClass: $MSresource->getCollectionItemInputDTOClass() ?? $LSresource->getCollectionItemInputDTOClass(),
            collectionItemOutputDTOClass: $MSresource->getCollectionItemOutputDTOClass() ?? $LSresource->getCollectionItemOutputDTOClass(),
            isActionDisabled: $MSresource->getIsActionDisabled() ?? $LSresource->getIsActionDisabled(),
            isSecurityCheckDisabled: $MSresource->getIsSecurityCheckDisabled() ?? $LSresource->getIsSecurityCheckDisabled(),
            isCRUD: $MSresource->getIsCRUD() ?? $LSresource->getIsCRUD(),
            voterAction: $MSresource->getVoterAction() ?? $LSresource->getVoterAction()
        );
    }

    /**
     * Updates Resource object with defaults.
     *
     * @param Resource $resource
     * @param \LSB\UtilityBundle\Manager\ManagerInterface|null $manager
     * @return Resource
     */
    public function updateResourceConfigurationWithDefaults(Resource $resource, ?ManagerInterface $manager = null): Resource
    {
        if (!$resource->getObjectClass() && $manager) {
            $resource->setObjectClass($manager->getResourceEntityClass());
        }

        if (!$resource->getInputCreateDTOClass(false)) {
            $resource->setInputCreateDTOClass($resource->getInputDTOClass());
        }

        if (!$resource->getInputUpdateDTOClass(false)) {
            $resource->setInputUpdateDTOClass($resource->getInputDTOClass());
        }

        if (!$resource->getCollectionItemOutputDTOClass()) {
            $resource->setCollectionItemOutputDTOClass($resource->getOutputDTOClass());
        }

        if (!$resource->getCollectionOutputDTOClass()) {
            $resource->setCollectionOutputDTOClass(BaseCollectionOutputDTO::class);
        }

        if (!$resource->getSerializationType()) {
            $resource->setSerializationType(Resource::TYPE_AUTO);
        }

        if (!$resource->getCollectionItemSerializationType()) {
            $resource->setCollectionItemSerializationType(Resource::TYPE_AUTO);
        }

        return $resource;
    }

    /**
     * @param \ReflectionProperty $reflectionProperty
     * @param bool $updateWithDefaults
     * @param \LSB\UtilityBundle\Attribute\Resource|null $LSResource
     * @return \LSB\UtilityBundle\Attribute\Resource|null
     */
    public function getResourceByProperty(
        \ReflectionProperty $reflectionProperty,
        bool                $updateWithDefaults = false,
        ?Resource           $LSResource = null
    ): ?Resource {
        $propertyAttributes = $reflectionProperty->getAttributes(Resource::class);
        $resource = null;

        /**
         * @var Resource|null $resource
         */
        foreach ($propertyAttributes as $entityAttribute) {
            if ($entityAttribute->getName() !== Resource::class) {
                continue;
            }
            $resource = $entityAttribute->newInstance();
        }

        if (!$resource && $reflectionProperty->getType()) {
            $resource = new Resource(
                outputDTOClass: $reflectionProperty->getType()->getName()
            );
        }

        if ($resource && $LSResource) {
            $resource = $this->mergeResource($LSResource, $resource);
        } elseif (!$resource && $LSResource) {
            $resource = $LSResource;
        }

        if ($resource && $updateWithDefaults) {
            $this->updateResourceConfigurationWithDefaults($resource);
        }

        return $resource;
    }

    public function getItemResource(string $class, ?\ReflectionProperty $reflectionProperty = null, bool $updateConfiguration = false): ?Resource
    {
        $itemResource = null;

        if ($reflectionProperty) {
            $itemResource = $this->getResourceByProperty(
                reflectionProperty: $reflectionProperty,
                updateWithDefaults: true,
                LSResource: $itemResource
            );
        }


        return $itemResource;
    }

    /**
     * @throws \Exception
     */
    public function getCollectionItemResource(
        ?Resource            $masterResource = null,
        ?\ReflectionProperty $reflectionProperty = null,
        bool                 $updateConfiguration = false
    ): ?Resource {
        $propertyAttributes = $reflectionProperty->getAttributes(Resource::class);
        $resource = null;

        /**
         * @var Resource|null $resource
         */
        foreach ($propertyAttributes as $entityAttribute) {
            if ($entityAttribute->getName() !== Resource::class) {
                continue;
            }
            $resource = $entityAttribute->newInstance();
        }

        if (!$resource && $masterResource) {
            $resource = new Resource(
                outputDTOClass: $masterResource->getCollectionItemOutputDTOClass(),
                serializationType: $masterResource->getCollectionItemSerializationType()
            );
        } elseif ($resource) {
            $resource
                ->setOutputDTOClass($resource->getCollectionItemOutputDTOClass())
                ->setSerializationType($resource->getCollectionItemSerializationType());
        }


        if ($resource && $masterResource) {
            $resource = $this->mergeResource($masterResource, $resource);
        } elseif (!$resource && $masterResource) {
            $resource = $masterResource;
        }

        if ($resource && $updateConfiguration) {
            $this->updateResourceConfigurationWithDefaults($resource);
        }

        if (!$resource instanceof Resource) {
            throw new \Exception(sprintf("Collection Resource is missing. Please add Resource attribute to %s. .", $reflectionProperty->getName()));
        }

        return $resource;
    }

}