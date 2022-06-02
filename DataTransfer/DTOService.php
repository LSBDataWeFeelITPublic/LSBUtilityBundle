<?php

namespace LSB\UtilityBundle\DataTransfer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Proxy\Proxy;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use LSB\UtilityBundle\Attribute\ConvertToObject;
use LSB\UtilityBundle\Attribute\DTOPropertyConfig;
use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\Controller\BaseApiController;
use LSB\UtilityBundle\DataTransfer\DataTransformer\DataTransformerService;
use LSB\UtilityBundle\DataTransfer\DataTransformer\EntityConverter;
use LSB\UtilityBundle\DataTransfer\Model\BaseDTO;
use LSB\UtilityBundle\DataTransfer\Model\DTOInterface;
use LSB\UtilityBundle\DataTransfer\Model\Input\InputDTOInterface;
use LSB\UtilityBundle\DataTransfer\Model\ObjectHolder;
use LSB\UtilityBundle\DataTransfer\Model\Output\OutputDTOInterface;
use LSB\UtilityBundle\DataTransfer\Request\RequestAttributes;
use LSB\UtilityBundle\DataTransfer\Request\RequestIdentifier;
use LSB\UtilityBundle\DataTransfer\Resource\ResourceHelper;
use LSB\UtilityBundle\Interfaces\IdInterface;
use LSB\UtilityBundle\Interfaces\UuidInterface;
use LSB\UtilityBundle\Manager\ManagerInterface;
use LSB\UtilityBundle\Repository\PaginationInterface as RepositoryPaginationInterface;
use LSB\UtilityBundle\Service\ManagerContainerInterface;
use Ramsey\Uuid\Nonstandard\Uuid;
use ReflectionClass;
use ReflectionProperty;
use Stringable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Webmozart\Assert\Assert;

class DTOService
{
    const METHOD_WORKFLOW_INPUT = 10;
    const METHOD_WORKFLOW_OUTPUT = 20;

    const NESTING_LEVEL_MAX = 1;
    const NESTING_LEVEL_BLOCKED = -100;
    const NESTING_LEVEL_ALLOWED = 0;

    public function __construct(
        protected ManagerContainerInterface     $managerContainer,
        protected DataTransformerService        $dataTransformerService,
        protected AuthorizationCheckerInterface $authorizationChecker,
        protected PaginatorInterface            $paginator,
        protected ResourceHelper                $resourceHelper,
        protected RequestStack                  $requestStack
    ) {
    }

    /**
     * @return \LSB\UtilityBundle\Service\ManagerContainerInterface
     */
    public function getManagerContainer(): ManagerContainerInterface
    {
        return $this->managerContainer;
    }

    public function remove(Resource $resource, $object, ?string $appCode = null)
    {
        $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());
        $manager->doRemove($object);
    }

    /**
     * @param \LSB\UtilityBundle\Attribute\Resource $resource
     * @param $inputDTO
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string|null $appCode
     * @return object
     * @throws \Exception
     */
    public function createNewFromDTO(Resource $resource, DTOInterface $inputDTO, Request $request, ?string $appCode = null): object
    {
        //No manager, object will not be persisted
        $manager = null;

        if ($resource->getManagerClass()) {
            $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());
        }

        if (!$inputDTO->isValid()) {
            throw new \Exception('DTO is invalid. Unable to create new object.');
        }

        if ($manager) {
            $object = $manager->createNew();
        } else {
            $object = new ($resource->getObjectClass());
        }

        $inputDTO->setIsNewObjectCreated(true);


        if ($resource->getSerializationType() === Resource::TYPE_DATA_TRANSFORMER) {
            $objectClass = $manager ? $manager->getResourceEntityClass() : $resource->getObjectClass();
            if (!$objectClass) {
                throw new \Exception('Object class is missing.');
            }

            $dataTransformer = $this->dataTransformerService->getDataTransformer($inputDTO, $objectClass, []);
        } else {
            $dataTransformer = null;
        }

        if ($dataTransformer) {
            $object = $dataTransformer->transform(
                $inputDTO,
                get_class($object),
                $this->dataTransformerService->buildDataTransformerContext($inputDTO, null, $object, $manager)
            );
        } else {
            $this->populateEntityWithDTO($inputDTO, $object, true, $request);
        }

        if ($manager) {
            $manager->doPersist($object);
            $manager->update($object);
        }

        return $object;
    }

    /**
     * @param \LSB\UtilityBundle\Attribute\Resource $resource
     * @param $inputDTO
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string|null $appCode
     * @return object
     * @throws \Exception
     */
    public function updateFromDTO(Resource $resource, $inputDTO, Request $request, ?string $appCode = null): object
    {

        if ($resource->getManagerClass()) {
            $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());
        } else {
            $manager = null;
        }


        if (!$inputDTO->isValid()) {
            throw new \Exception('DTO is invalid. Unable to create new object.');
        }

        $object = $inputDTO->getObject();

        if (!$object) {
            throw new \Exception('Object is required for updateFromDTO method.');
        }


        if ($resource->getSerializationType() === Resource::TYPE_DATA_TRANSFORMER) {
            $dataTransformer = $this->dataTransformerService->getDataInitializerTransformer($inputDTO, $manager->getResourceEntityClass(), []);
        } else {
            $dataTransformer = null;
        }

        if ($dataTransformer) {
            $object = $dataTransformer->transform(
                $inputDTO,
                get_class($object),
                $this->dataTransformerService->buildDataTransformerContext($inputDTO, null, $object, $manager)
            );
        } else {
            $this->populateEntityWithDTO($inputDTO, $object, true, $request);
        }

        if ($manager) {
            $manager->update($object);
        }

        return $object;
    }

    /**
     * @param \LSB\UtilityBundle\Attribute\Resource $resource
     * @param object $object
     * @param \LSB\UtilityBundle\DataTransfer\Model\Output\OutputDTOInterface|null $outputDTO
     * @param null $deserializationType
     * @param int $level
     * @param bool $isCollectionItem
     * @return \LSB\UtilityBundle\DataTransfer\Model\Output\OutputDTOInterface|null
     * @throws \ReflectionException
     */
    public function generateOutputDTO(
        Resource            $resource,
        object              $object,
        ?OutputDTOInterface $outputDTO = null,
                            $deserializationType = null,
        int                 $level = 0,
        bool                $isCollectionItem = false
    ): ?OutputDTOInterface {
        //TODO add max level to resource configuration
        if ($level >= self::NESTING_LEVEL_MAX || $level === self::NESTING_LEVEL_BLOCKED) {
            return null;
        }

        $isIterable = is_iterable($object);
        $processAsCollection = $resource->getIsCollection() && $isIterable;

        if ($isIterable) {
            if (!$outputDTO) {
                $outputDTO = new ($resource->getCollectionOutputDTOClass())();
            }
        } elseif ($isCollectionItem) {
            $outputDTO = new ($resource->getCollectionItemOutputDTOClass())();
        }

        if (!$outputDTO) {
            $outputDTO = new ($resource->getOutputDTOClass())();
        }

        if (!$outputDTO instanceof DTOInterface) {
            throw new \Exception('Output DTO class must implement OutputDTOInterface');
        }

        if (!$deserializationType) {
            $deserializationType = $resource->getSerializationType();
        }

        if ($processAsCollection) {
            if ($deserializationType === Resource::TYPE_DATA_TRANSFORMER) {
                if (!is_iterable($object)) {
                    throw new \Exception('Object must be iterable');
                }

                if ($object instanceof PaginationInterface) {
                    //Pagination
                    $items = [];

                    foreach ($object->getItems() as $item) {

                        $itemOutputDTOClass = $resource->getCollectionItemOutputDTOClass();

                        $itemOutputDTO = (new $itemOutputDTOClass);

                        if (!$itemOutputDTO instanceof OutputDTOInterface) {
                            throw new \Exception('Output DTO class must implement OutputDTOInterface');
                        }

                        $items[] = $this->generateOutputDTO(
                            $resource,
                            $item,
                            $itemOutputDTO,
                            $resource->getCollectionItemSerializationType(),
                            $level + 1,
                            true
                        );
                    }

                    $object->setItems($items);
                    $items = null;

                } else {
                    throw new \Exception('Not supported');
                }

                $dataTransformer = $this->dataTransformerService->getDataTransformer($object, get_class($outputDTO), []);
                if ($dataTransformer) {
                    $outputDTO = $dataTransformer->transform(
                        $object,
                        get_class($outputDTO),
                        $this->dataTransformerService->buildDataTransformerContext(null, $outputDTO, null, null)
                    );
                } else {
                    throw new \Exception('Missing data transformer.');
                }
            } else {
                $this->populateDtoWithEntity(targetObject: $object, requestDTO: $outputDTO, resource: $resource);
            }
        } else {
            if ($deserializationType === Resource::TYPE_DATA_TRANSFORMER) {
                $dataTransformer = $this->dataTransformerService->getDataTransformer($object, get_class($outputDTO), []);
                if ($dataTransformer) {
                    $outputDTO = $dataTransformer->transform(
                        $object,
                        get_class($outputDTO),
                        $this->dataTransformerService->buildDataTransformerContext(null, $outputDTO, null, null)
                    );
                } else {
                    throw new \Exception('Missing data transformer.');
                }
            } else {
                $this->populateDtoWithEntity(targetObject: $object, requestDTO: $outputDTO, resource: $resource);
            }
        }

        return $outputDTO;
    }

    /**
     * @param \LSB\UtilityBundle\Attribute\Resource $resource
     * @param object $object
     * @return \LSB\UtilityBundle\DataTransfer\Model\Output\OutputDTOInterface
     * @throws \Exception
     * @deprecated Draft
     */
    public function generateCollectionItemOutputDTO(Resource $resource, object $object): OutputDTOInterface
    {
        $outputDTO = new ($resource->getCollectionOutputDTOClass())();

        if ($resource->getIsCollection()) {
            //In case of data collection data transformation is required
            if ($resource->getSerializationType() !== Resource::TYPE_DATA_TRANSFORMER) {
                throw new \Exception('Data transformed is required for collection');
            }

            if (!is_iterable($object)) {
                throw new \Exception('Object must be iterable');
            }

            if ($object instanceof PaginationInterface) {
                //Pagination
                foreach ($object->getItems() as $item) {

                }

            } else {
                throw new \Exception('Not supported');
            }

        } else {
            if ($resource->getSerializationType() === Resource::TYPE_DATA_TRANSFORMER) {
                $dataTransformer = $this->dataTransformerService->getDataTransformer($object, get_class($outputDTO), []);
                if ($dataTransformer) {
                    $outputDTO = $dataTransformer->transform(
                        $object,
                        get_class($outputDTO),
                        $this->dataTransformerService->buildDataTransformerContext(null, $outputDTO, null, null)
                    );
                }
            } else {
                $this->populateDtoWithEntity(targetObject: $object, requestDTO: $outputDTO);
            }
        }


        return $outputDTO;
    }

    /**
     * @param \LSB\UtilityBundle\Attribute\Resource $resource
     * @param \LSB\UtilityBundle\DataTransfer\Request\RequestIdentifier $requestIdentifier
     * @param bool $allowId
     * @return object|null
     * @throws \ReflectionException
     */
    public function getObjectByRequestId(Resource $resource, RequestIdentifier $requestIdentifier, bool $allowId = false): ?object
    {
        $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());

        $entityClass = $manager->getResourceEntityClass();
        $idName = $requestIdentifier->getIdentifierName();
        $id = $requestIdentifier->getValue();
        $reflectionClass = new ReflectionClass($entityClass);

        if (($reflectionClass->implementsInterface(UuidInterface::class)
                || $reflectionClass->hasProperty('uuid')
            )
            && $idName === RequestAttributes::IDENTIFIER_ATTRIBUTE_UUID
            && !is_numeric($id)
        ) {
            $object = $manager->getByUuid($id);
        } elseif ($allowId) {
            $object = $manager->getById($id);
        } else {
            $object = null;
        }

        return $object;
    }

    /**
     * At this stage, we populate the empty DTO object with data from the entity, create the default content of the DTO object, on which we will later overlay the data from the user
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function generateInputDTO(
        Resource           $resource,
        ?RequestIdentifier $requestIdentifier = null,
        ?InputDTOInterface $inputDTO = null,
        bool               $populate = true,
        bool               $createNewObject = false,
        int                $level = 0,
        bool               $isCollectionItem = false,
        ?object            $object = null
    ): InputDTOInterface {

        $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());
        if ($resource->getIsTranslation()) {
            $entityClass = $manager->getResourceTranslationClass();
        } else {
            $entityClass = $manager->getResourceEntityClass();
        }

        if (!$object) {
            if (!$requestIdentifier) {
                throw new \Exception('Request identifier is required.');
            }

            $object = $this->getObjectByRequestId($resource, $requestIdentifier);
        }

        $isIterable = is_iterable($object);
        $processAsCollection = $resource->getIsCollection() && $isIterable;

        if (!$inputDTO) {
            if ($isCollectionItem) {
                $inputDTO = new ($resource->getCollectionItemInputDTOClass())();
            }

            if (!$inputDTO) {
                $inputDTO = new ($resource->getInputDTOClass())();
            }
        }

        if (!$inputDTO instanceof InputDTOInterface) {
            throw new \Exception('Input DTO class must implement InputDTOInterface');
        }

        //Verify object class
        if (!$createNewObject && !$object instanceof $entityClass) {
            throw new \Exception('Object does not exist.');
        } elseif ($createNewObject && !$object instanceof $entityClass) {
            if ($resource->getIsTranslation()) {
                $inputDTO->setIsNewObjectCreated(true);
                $object = $manager->createNewTranslation();
            } else {
                $inputDTO->setIsNewObjectCreated(true);
                $object = $manager->createNew();
            }
        }

        $inputDTO->setObject($object);

        if ($populate) {
            switch ($resource->getSerializationType()) {
                case Resource::TYPE_AUTO:
                    $this->populateDtoWithEntity(
                        targetObject: $object,
                        requestDTO: $inputDTO,
                        workflow: DTOService::METHOD_WORKFLOW_INPUT
                    );
                    break;

                case Resource::TYPE_DATA_TRANSFORMER:
                    $dataTransformer = $this->dataTransformerService->getDataInitializerTransformer($inputDTO, $manager->getResourceEntityClass(), []);
                    if ($dataTransformer) {
                        $inputDTO = $dataTransformer->initialize(
                            get_class($inputDTO),
                            $this->dataTransformerService->buildDataInitializerTransformerContext($inputDTO, $object, $manager)
                        );
                    } else {
                        throw new \Exception('Data initializer was not found.');
                    }
                    break;
            }
        }


        return $inputDTO;
    }

    /**
     * @param \LSB\UtilityBundle\Attribute\Resource $resource
     * @param object $object
     * @param \LSB\UtilityBundle\DataTransfer\Model\Output\OutputDTOInterface $requestDTO
     * @param bool $populate
     * @return \LSB\UtilityBundle\DataTransfer\Model\Output\OutputDTOInterface
     * @throws \ReflectionException
     */
    public function prepareOutputDTO(
        Resource           $resource,
        object             $object,
        OutputDTOInterface $requestDTO,
        bool               $populate = true
    ): OutputDTOInterface {

        if ($populate) {
            $this->populateDtoWithEntity(
                targetObject: $object,
                requestDTO: $requestDTO
            );
        }

        //The entity will be used later.
        $requestDTO->setObject($object);

        return $requestDTO;
    }

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

    /**
     * @param \LSB\UtilityBundle\Attribute\Resource $resource
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $action
     * @param $subject
     * @return bool
     */
    public function isGranted(Resource $resource, Request $request, string $action, $subject = null): bool
    {
        if ($resource->getManagerClass()) {
            $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());
        } else {
            $manager = null;
        }

        return $this->authorizationChecker->isGranted($action, $manager?->getVoterSubject($subject, $this->getAppCode($request)));
    }

    /**
     * @param string $managerClass
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $action
     * @param $subject
     * @return bool
     */
    public function isSubjectGranted(string $managerClass, Request $request, string $action, $subject = null): bool
    {
        $manager = $this->managerContainer->getByManagerClass($managerClass);
        return $this->authorizationChecker->isGranted($action, $manager->getVoterSubject($subject, $this->getAppCode($request)));
    }

    /**
     * @param \LSB\UtilityBundle\Attribute\Resource $resource
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     */
    public function paginateCollection(Resource $resource, Request $request): PaginationInterface
    {
        $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());
        return $this->paginate($this->paginator, $manager->getRepository(), $request);
    }

    /**
     * @param \Knp\Component\Pager\PaginatorInterface $paginator
     * @param \LSB\UtilityBundle\Repository\PaginationInterface $repository
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $qbAlias
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     */
    protected function paginate(
        PaginatorInterface            $paginator,
        RepositoryPaginationInterface $repository,
        Request                       $request,
        string                        $qbAlias = RepositoryPaginationInterface::DEFAULT_ALIAS
    ): PaginationInterface {
        return $paginator->paginate(
            $repository->getPaginationQueryBuilder(),
            $request->query->getInt(BaseApiController::REQUEST_QUERY_PARAMETER_PAGE, BaseApiController::DEFAULT_PAGE),
            $request->query->getInt(BaseApiController::REQUEST_QUERY_PARAMETER_LIMIT, BaseApiController::DEFAULT_LIMIT)
        );
    }

    /**
     * @param \LSB\UtilityBundle\Attribute\Resource $resource
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param iterable $collection
     * @param string $actionName
     * @return bool
     */
    public function checkCollection(Resource $resource, Request $request, iterable $collection, string $actionName): bool
    {
        $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());
        $appCode = $this->getAppCode($request);

        foreach ($collection as $item) {
            $isGranted = $this->authorizationChecker->isGranted($actionName, $manager->getVoterSubject($item, $appCode));
            if (!$isGranted) {
                return false;
            }
        }

        return true;
    }

    public function getRealClass(object $object): ?string
    {
        $class = get_class($object);

        if (!$object instanceof Proxy) {
            return $class;
        }

        try {
            $reflectionClass = new \ReflectionClass($class);
        } catch (\Exception $e) {
            return null;
        }

        return $reflectionClass->getParentClass() ? $reflectionClass->getParentClass()->getName() : null;
    }

    /**
     * @param string $class
     * @param bool $updateWithDefaults
     * @return \LSB\UtilityBundle\Attribute\Resource|null
     * @deprecated
     */
    public function getResourceByEntity(string $class, bool $updateWithDefaults = false): ?Resource
    {
        try {
            $reflectionClass = new ReflectionClass($class);
        } catch (\Exception $e) {
            return null;
        }

        $entityAttributes = $reflectionClass->getAttributes(Resource::class);
        $entityResource = null;

        /**
         * @var Resource|null $entityResource
         */
        foreach ($entityAttributes as $entityAttribute) {
            if ($entityAttribute->getName() !== Resource::class) {
                continue;
            }
            $entityResource = $entityAttribute->newInstance();
        }

//        if ($entityResource) {
//            $manager = $this->managerContainer->getByManagerClass($entityResource->getManagerClass());
//        }

        if ($entityResource && $updateWithDefaults) {
            $this->resourceHelper->updateResourceConfigurationWithDefaults($entityResource);
        }

        return $entityResource;
    }

    /**
     * @param \ReflectionProperty $reflectionProperty
     * @param bool $updateWithDefaults
     * @param \LSB\UtilityBundle\Attribute\Resource|null $LSResource
     * @return \LSB\UtilityBundle\Attribute\Resource|null
     *
     */
    public function getResourceByProperty(ReflectionProperty $reflectionProperty, bool $updateWithDefaults = false, ?Resource $LSResource = null): ?Resource
    {
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
            $resource = $this->resourceHelper->mergeResource($LSResource, $resource);
        } elseif (!$resource && $LSResource) {
            $resource = $LSResource;
        }

        if ($resource && $updateWithDefaults) {
            $this->resourceHelper->updateResourceConfigurationWithDefaults($resource);
        }

        return $resource;
    }

    /**
     * @param string $class
     * @param \ReflectionProperty|null $reflectionProperty
     * @param bool $updateConfiguration
     * @return \LSB\UtilityBundle\Attribute\Resource|null
     * @throws \Exception
     */
    public function getItemResource(string $class, ?ReflectionProperty $reflectionProperty = null, bool $updateConfiguration = false): ?Resource
    {
        $itemResource = null;

        if ($reflectionProperty) {
            $itemResource = $this->getResourceByProperty($reflectionProperty, true, $itemResource);
        }


        return $itemResource;
    }

    public function getCollectionItemResource(
        ?Resource           $masterResource = null,
        ?ReflectionProperty $reflectionProperty = null,
        bool                $updateConfiguration = false
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
            $resource = $this->resourceHelper->mergeResource($masterResource, $resource);
        } elseif (!$resource && $masterResource) {
            $resource = $masterResource;
        }

        if ($resource && $updateConfiguration) {
            $this->resourceHelper->updateResourceConfigurationWithDefaults($resource);
        }

        if (!$resource instanceof Resource) {
            throw new \Exception(sprintf("Collection Resource is missing. Please add Resource attribute to %s. .", $reflectionProperty->getName()));
        }

        return $resource;
    }

    public static array $excludedProps = ['errors'];

    /**
     * @param InputDTOInterface $dto
     * @param object $targetObject
     * @param bool $convertIdsIntoEntities
     * @param \Symfony\Component\HttpFoundation\Request|null $request
     * @param string|null $appCode
     * @param bool $allowNestedObject
     * @throws \Exception
     */
    public function populateEntityWithDTO(
        DTOInterface $dto,
        object       $targetObject,
        bool         $convertIdsIntoEntities = true,
        ?Request     $request = null,
        ?string      $appCode = null,
        bool         $allowNestedObject = true
    ): void {
        if (!$request) {
            $request = $this->requestStack->getCurrentRequest();
        }

        $propertiesFilter = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE;

        $reflectionDTO = new ReflectionClass($dto);
        $reflectionProperties = $reflectionDTO->getProperties($propertiesFilter);
        $nestingLevel = $allowNestedObject ? self::NESTING_LEVEL_ALLOWED : self::NESTING_LEVEL_BLOCKED;

        /**
         * @var ReflectionProperty $reflectionProperty
         */
        foreach ($reflectionProperties as $reflectionProperty) {
            if (array_search($reflectionProperty->getName(), self::$excludedProps) !== false) {
                continue;
            }
            $DTOObjectGetter = null;
            $setterMethod = null;
            $DTOPropertyName = $reflectionProperty->getName();
            $targetObjectPropertyName = $reflectionProperty->getName();

            $DTOPropertyConfig = self::getDTOPropertyConfig($reflectionProperty);

            if ($DTOPropertyConfig) {
                if ($DTOPropertyConfig->getDTOGetter()) {
                    $DTOObjectGetter = $DTOPropertyConfig->getDTOGetter();
                }

                if ($DTOPropertyConfig->getObjectSetter()) {
                    $setterMethod = $DTOPropertyConfig->getObjectSetter();
                }

                if ($DTOPropertyConfig->getObjectPropertyName()) {
                    $targetObjectPropertyName = $DTOPropertyConfig->getObjectPropertyName();
                }

                if ($DTOPropertyConfig->getDTOPropertyName()) {
                    $DTOPropertyName = $DTOPropertyConfig->getDTOPropertyName();
                }
            }

            $propertyAccessor = PropertyAccess::createPropertyAccessor();

            if ($setterMethod && !method_exists($targetObject, $setterMethod)) {
                throw new \Exception(sprintf("Setter %s does not exist", $setterMethod));
            }

            if ($DTOObjectGetter) {
                if (!method_exists($dto, $DTOObjectGetter)) {
                    throw new \Exception(sprintf("Getter %s not exists.", $DTOObjectGetter));
                }
                $dtoValue = $dto->$DTOObjectGetter();
            } elseif ($propertyAccessor->isReadable($dto, $DTOPropertyName)) {
                $dtoValue = $propertyAccessor->getValue($dto, $DTOPropertyName);
            } else {
                continue;
            }


            if ($convertIdsIntoEntities && $this->hasConvertToObjectAttribute($reflectionProperty)) {
                $objectHolder = $this->convertValueToObjectHolder(
                    request: $request,
                    reflectionProperty: $reflectionProperty,
                    dto: $dto,
                    getterName: $DTOObjectGetter,
                    propertyName: $DTOPropertyName
                );

                if ($objectHolder instanceof ObjectHolder) {
                    $targetValue = $objectHolder->getObject();
                } else {
                    $targetValue = null;
                }
            } else {
                $targetValue = $dtoValue;
            }

            if ($setterMethod) {
                $targetObject->$setterMethod($targetValue);
            } elseif ($propertyAccessor->isWritable($targetObject, $targetObjectPropertyName)) {
                $propertyAccessor->setValue($targetObject, $targetObjectPropertyName, $targetValue);
            } else {
                continue;
            }
        }
    }

    /**
     * Support for DTO autofilling for DTO and Entity coexisting properties
     * Used by InputDTO and OutputDTO objects
     *
     * @param object $targetObject
     * @param \LSB\UtilityBundle\DataTransfer\Model\BaseDTO $requestDTO
     * @param \LSB\UtilityBundle\Attribute\Resource|null $resource
     * @param bool $allowNestedObject
     * @param int $workflow
     * @throws \ReflectionException
     */
    public function populateDtoWithEntity(
        object    $targetObject,
        BaseDTO   $requestDTO,
        ?Resource $resource = null,
        bool      $allowNestedObject = true,
        int       $workflow = self::METHOD_WORKFLOW_OUTPUT

    ): void {
        $propertiesFilter = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE;
        $reflectionDTO = new ReflectionClass($requestDTO);
        $reflectionPropertiesDTO = $reflectionDTO->getProperties($propertiesFilter);
        $nestingLevel = $allowNestedObject ? self::NESTING_LEVEL_ALLOWED : self::NESTING_LEVEL_BLOCKED;

        /**
         * @var ReflectionProperty $reflectionPropertyDTO
         */
        foreach ($reflectionPropertiesDTO as $reflectionPropertyDTO) {
            if (array_search($reflectionPropertyDTO->getName(), self::$excludedProps) !== false) {
                continue;
            }

            $DTOPropertyConfig = self::getDTOPropertyConfig($reflectionPropertyDTO);

            $targetObjectGetter = null;
            $setterMethod = null;
            $DTOPropertyName = $reflectionPropertyDTO->getName();
            $targetObjectPropertyName = $reflectionPropertyDTO->getName();

            if ($DTOPropertyConfig) {
                if ($DTOPropertyConfig->getObjectGetter()) {
                    $targetObjectGetter = $DTOPropertyConfig->getObjectGetter();
                }

                if ($DTOPropertyConfig->getDTOSetter()) {
                    $setterMethod = $DTOPropertyConfig->getDTOSetter();
                }

                if ($DTOPropertyConfig->getDTOSetter()) {
                    $setterMethod = $DTOPropertyConfig->getDTOSetter();
                }

                if ($DTOPropertyConfig->getObjectPropertyName()) {
                    $targetObjectPropertyName = $DTOPropertyConfig->getObjectPropertyName();
                }

                if ($DTOPropertyConfig->getDTOPropertyName()) {
                    $DTOPropertyName = $DTOPropertyConfig->getDTOPropertyName();
                }
            }

            $propertyAccessor = PropertyAccess::createPropertyAccessor();


            if ($setterMethod && !method_exists($requestDTO, $setterMethod)) {
                throw new \Exception(sprintf("Setter method %s not found", $setterMethod));
            }

            if ($targetObjectGetter && !method_exists($targetObject, $targetObjectGetter)) {
                throw new \Exception(sprintf("Getter method %s not found", $targetObjectGetter));
            }

            if ($targetObjectGetter) {
                $value = $targetObject->$targetObjectGetter();
            } elseif ($propertyAccessor->isReadable($targetObject, $targetObjectPropertyName)) {
                $value = $propertyAccessor->getValue($targetObject, $targetObjectPropertyName);
            } else {
                continue;
            }

            if (!is_object($value) && !is_iterable($value) || is_object($value) && $this->isStandardObject($value)) {
                $valueDTO = $value;
            } elseif (is_object($value) && !is_iterable($value)) {
                $itemResource = $this->getItemResource($this->getRealClass($value), $reflectionPropertyDTO, true);

                if ($workflow === self::METHOD_WORKFLOW_OUTPUT) {
                    $valueDTO = $this->generateOutputDTO(
                        resource: $itemResource,
                        object: $value,
                        level: $nestingLevel
                    );
                } else {
                    $valueDTO = null;
                }

            } elseif (is_iterable($value)) {
                $items = [];

                $itemResource = $this->getCollectionItemResource($resource, $reflectionPropertyDTO, true);

                if ($workflow === self::METHOD_WORKFLOW_OUTPUT) {
                    foreach ($value as $item) {
                        $itemDTO = $this->generateOutputDTO(
                            resource: $itemResource,
                            object: $item,
                            level: $nestingLevel,
                            isCollectionItem: true
                        );
                        $items[] = $itemDTO;
                    }
                } else {
                    foreach ($value as $item) {
                        $itemDTO = $this->generateInputDTO(
                            resource: $itemResource,
                            createNewObject: true,
                            level: $nestingLevel,
                            isCollectionItem: true,
                            object: $item
                        );

                        $items[] = $itemDTO;
                    }
                }

                $valueDTO = $items;

            } else {
                $valueDTO = null;
            }

            $value = null;

            if ($setterMethod) {
                $requestDTO->$setterMethod($value);
            } elseif ($propertyAccessor->isWritable($requestDTO, $DTOPropertyName)) {
                $propertyAccessor->setValue($requestDTO, $DTOPropertyName, $valueDTO);
            } else {
                continue;
            }
        }
    }

    protected function isStandardObject(object $object): bool
    {
        switch (true) {
            case $object instanceof \DateTime:
            case $object instanceof \StdClass:
                return true;

        }

        return false;
    }

    /**
     * @param $value
     * @return array|int|mixed|string
     */
    public function flattenValue($value)
    {
        // Flattening mechanism, disabled
        if (is_object($value)) {
            if ($value instanceof Uuid) {
                $value = (string)$value;
            } elseif ($value instanceof UuidInterface) {
                $value = (string)$value->getUuid();
            } elseif ($value instanceof IdInterface) {
                $value = (int)$value->getId();
            } elseif ($value instanceof Collection) {
                $flatValue = [];

                foreach ($value as $valum) {
                    $flatValue = $valum instanceof UuidInterface ? $valum->getUuid() : $valum->getId();
                }

                $value = $flatValue;
            }
        }

        return $value;
    }

    /**
     * @param string $propertyName
     * @return string
     */
    public static function generateSetterName(string $propertyName): string
    {
        return 'set' . self::classify($propertyName);
    }

    /**
     * @param string $propertyName
     * @return array
     */
    public static function generateGetterNames(string $propertyName): array
    {
        $prefixes = ['get', 'is'];

        $setterNames = [];

        foreach ($prefixes as $prefix) {
            $setterNames[] = $prefix . self::classify($propertyName);
        }

        return $setterNames;
    }

    /**
     * @param string $word
     * @return string
     */
    public static function camelize(string $word): string
    {
        return lcfirst(self::classify($word));
    }

    /**
     * @param string $word
     * @return string
     */
    public static function classify(string $word): string
    {
        return str_replace([' ', '_', '-'], '', ucwords($word, ' _-'));
    }

    /**
     * @param \ReflectionProperty $reflectionProperty
     * @return bool
     */
    protected function hasConvertToObjectAttribute(ReflectionProperty $reflectionProperty): bool
    {
        $attributes = $reflectionProperty->getAttributes(ConvertToObject::class);

        if (count($attributes) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param \ReflectionProperty $reflectionProperty
     * @param \LSB\UtilityBundle\DataTransfer\Model\DTOInterface $dto
     * @param string|null $getterName
     * @param string|null $propertyName
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string|null $appCode
     * @return \LSB\UtilityBundle\DataTransfer\Model\ObjectHolder|null
     * @throws \Exception
     */
    public function convertValueToObjectHolder(
        Request             $request,
        ?ReflectionProperty $reflectionProperty = null,
        ?DTOInterface       $dto = null,
        ?string             $getterName = null,
        ?string             $propertyName = null,
        ?string             $appCode = null,
        int                 $level = 0,
        bool                $isCollectionItem = false,
        ?ConvertToObject    $convertToObjectAttribute = null,
        int|string|object   $data = null
    ): ?ObjectHolder {

        if (!$convertToObjectAttribute) {
            if (!$reflectionProperty) {
                return null;
            }

            $attributes = $reflectionProperty->getAttributes(ConvertToObject::class);

            if (count($attributes) === 0) {
                return null;
            }

            /** @var ConvertToObject $convertToObjectAttribute */
            $convertToObjectAttribute = $attributes[0]->newInstance();
        }

        if ($convertToObjectAttribute->getManagerClass()) {
            $manager = $this->getManagerContainer()->getByManagerClass($convertToObjectAttribute->getManagerClass());

            if (!$manager instanceof ManagerInterface) {
                return null;
            }

            if (!$data) {
                if (!$propertyName) {
                    $propertyName = $reflectionProperty->getName();
                }

                $propertyAccessor = new PropertyAccessor();

                if ($getterName) {
                    $data = $dto->$getterName();
                } elseif ($propertyName) {
                    if (!$propertyAccessor->isReadable($dto, $propertyName)) {
                        throw new \Exception(sprintf('Property %s is not readable.', $propertyName));
                    }

                    $data = $propertyAccessor->getValue($dto, $propertyName);
                } else {
                    throw new \Exception('Getter or property name is required.');
                }
            }

            if (is_iterable($data)) {
                $items = $data instanceof Collection ? new ArrayCollection() : [];

                foreach ($data as $datum) {

                    $objectHolder = $this->convertValueToObjectHolder(
                        reflectionProperty: null,
                        dto: null,
                        getterName: $getterName,
                        propertyName: null,
                        request: $request,
                        isCollectionItem: true,
                        convertToObjectAttribute: $convertToObjectAttribute,
                        data: $datum,
                        level: $level + 1
                    );

                    if ($objectHolder) {
                        if ($items instanceof ArrayCollection) {
                            $items->add($objectHolder->getObject());
                        } else {
                            $items[] = $objectHolder->getObject();
                        }
                    }
                }

                return new ObjectHolder(null, $items);

            } else {
                if ($data && !is_object($data) && (is_string($data) || is_scalar($data))
                    || $data instanceof Stringable
                ) {
                    $id = $data;
                } else {
                    $id = null;
                }

                if ($id) {
                    switch ($convertToObjectAttribute->getKey()) {
                        case ConvertToObject::KEY_ID:
                            $id = (int)$id;
                            $object = $manager->getById($id);
                            break;
                        case ConvertToObject::KEY_UUID:
                            $id = (string)$id;
                            try {
                                Assert::uuid($id);
                            } catch (\Exception $e) {
                                return null;
                            }

                            $object = $manager->getByUuid($id);
                            break;
                        default:
                            $id = null;
                            $object = null;
                    }
                } elseif ($data instanceof DTOInterface && $data->getObject()) {
                    $object = $data->getObject();
                } else {
                    if ($convertToObjectAttribute->isTranslation()) {
                        $object = $manager->createNewTranslation();
                    } else {
                        $object = $manager->createNew();
                    }

                    if ($data instanceof DTOInterface) {
                        $data->setIsNewObjectCreated(true);
                    }
                }

                //Sprawdzamy czy utworzony obiekt należy zasilić danymi

                if ($data instanceof DTOInterface && $object) {
                    // narazie populate nie jest potrzebne (if)
//                    if ($populate) {

                    $this->populateEntityWithDTO(
                        dto: $data,
                        targetObject: $object,
                        convertIdsIntoEntities: true,
                        request: $request,
                        appCode: $appCode,
                        allowNestedObject: false
                    );

                    $data->setObject($object);
//                    }
                }

                if ($object) {
                    //Optional type check
                    if ($convertToObjectAttribute->getObjectClass() && !$object instanceof ($convertToObjectAttribute->getObjectClass())) {
                        throw new \Exception(sprintf("Wrong type. Expected %s got %s", $convertToObjectAttribute->getObjectClass(), get_class($object)));
                    }

                    //Optional security check
                    if ($convertToObjectAttribute->getVoterAction()) {
                        if (!$this->isSubjectGranted($convertToObjectAttribute->getManagerClass(), $request, $convertToObjectAttribute->getVoterAction(), $object)) {
                            throw new \Exception(sprintf("Access denied. Class: %s, object: %s", $convertToObjectAttribute->getObjectClass(), $id));
                        }
                    }

                    return new ObjectHolder($id, $object);
                } elseif ($id && $convertToObjectAttribute->isThrowNotFoundException()) {
                    throw new \Exception(sprintf("Property: %s; Object %s has not been found. ", $reflectionProperty->getName(), $id));
                }
            }
        }
        return null;
    }

    /**
     * @param \ReflectionProperty $reflectionProperty
     * @return \LSB\UtilityBundle\Attribute\DTOPropertyConfig|null
     */
    public function getDTOPropertyConfig(ReflectionProperty $reflectionProperty): ?DTOPropertyConfig
    {
        $attributes = $reflectionProperty->getAttributes(DTOPropertyConfig::class);

        if (count($attributes) === 0) {
            return null;
        }

        /**
         * @var DTOPropertyConfig $attribute
         */
        $attribute = $attributes[0]->newInstance();
        return $attribute instanceof DTOPropertyConfig ? $attribute : null;
    }
}