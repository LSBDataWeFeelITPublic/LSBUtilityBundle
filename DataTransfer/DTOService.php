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
use LSB\UtilityBundle\DataTransfer\DataTransformer\DataTransformerInterface;
use LSB\UtilityBundle\DataTransfer\DataTransformer\DataTransformerService;
use LSB\UtilityBundle\DataTransfer\Helper\App\AppHelper;
use LSB\UtilityBundle\DataTransfer\Helper\Authorization\AuthorizationHelper;
use LSB\UtilityBundle\DataTransfer\Helper\Collection\CollectionHelper;
use LSB\UtilityBundle\DataTransfer\Helper\Deserializer\DTODeserializerInterface;
use LSB\UtilityBundle\DataTransfer\Helper\Output\OutputHelper;
use LSB\UtilityBundle\DataTransfer\Helper\Serializer\DTOSerializerInterface;
use LSB\UtilityBundle\DataTransfer\Helper\Validator\DTOValidatorInterface;
use LSB\UtilityBundle\DataTransfer\Model\DTOInterface;
use LSB\UtilityBundle\DataTransfer\Model\Input\InputDTOInterface;
use LSB\UtilityBundle\DataTransfer\Model\ObjectHolder;
use LSB\UtilityBundle\DataTransfer\Model\Output\OutputDTOInterface;
use LSB\UtilityBundle\DataTransfer\Request\RequestAttributes;
use LSB\UtilityBundle\DataTransfer\Request\RequestData;
use LSB\UtilityBundle\DataTransfer\Request\RequestIdentifier;
use LSB\UtilityBundle\DataTransfer\Helper\Resource\ResourceHelper;
use LSB\UtilityBundle\Interfaces\IdInterface;
use LSB\UtilityBundle\Interfaces\UuidInterface;
use LSB\UtilityBundle\Manager\ManagerInterface;
use LSB\UtilityBundle\Service\ManagerContainerInterface;
use LSB\UtilityBundle\Value\Value;
use Money\Money;
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

    const NESTING_LEVEL_MAX = 12;
    const NESTING_LEVEL_BLOCKED = -100;
    const NESTING_LEVEL_ALLOWED = 0;

    public function __construct(
        protected ManagerContainerInterface     $managerContainer,
        protected DataTransformerService        $dataTransformerService,
        protected AuthorizationCheckerInterface $authorizationChecker,
        protected ResourceHelper                $resourceHelper,
        protected RequestStack                  $requestStack,
        protected DTOValidatorInterface         $DTOValidator,
        protected DTODeserializerInterface      $DTODeserializer,
        protected DTOSerializerInterface        $DTOSerializer,
        protected AppHelper                     $appHelper,
        protected AuthorizationHelper           $authorizationHelper,
        protected CollectionHelper              $collectionHelper
    ) {
    }

    /**
     * @return \LSB\UtilityBundle\Service\ManagerContainerInterface
     */
    public function getManagerContainer(): ManagerContainerInterface
    {
        return $this->managerContainer;
    }

    public function remove(Resource $resource, $object, ?string $appCode = null): void
    {
        $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());
        $manager->doRemove($object);
    }

    /**
     * @throws \Exception
     */
    public function createNewFromDTO(Resource $resource, InputDTOInterface $inputDTO, Request $request, ?string $appCode = null): object
    {
        $manager = $this->getManagerByResource($resource);

        if (!$inputDTO->isValid()) {
            throw new \Exception('DTO is invalid. Unable to create new object.');
        }

        $object = $this->createNewObject($resource, $manager);

        $inputDTO->setIsNewObjectCreated(true);

        if ($dataTransformer = $this->getDataTransformer($inputDTO, $resource, $manager)) {
            $object = $dataTransformer->transform(
                $inputDTO,
                get_class($object),
                $this->dataTransformerService->buildDataTransformerContext($inputDTO, null, $object, $manager)
            );
        } else {
            $this->populateEntityWithDTO($inputDTO, $object, true, $request);
        }

        $this->updateWithManager($manager, $object);

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
        $manager = $this->getManagerByResource($resource);

        if (!$inputDTO->isValid()) {
            throw new \Exception('DTO is invalid. Unable to create new object.');
        }

        $object = $inputDTO->getObject();

        if (!$object) {
            throw new \Exception('Object is required for updateFromDTO method.');
        }

        if ($dataTransformer = $this->getDataTransformer($inputDTO, $resource, $manager)) {
            $object = $dataTransformer->transform(
                $inputDTO,
                get_class($object),
                $this->dataTransformerService->buildDataTransformerContext($inputDTO, null, $object, $manager)
            );
        } else {
            $this->populateEntityWithDTO($inputDTO, $object, true, $request);
        }

        $manager?->update($object);

        return $object;
    }

    protected function createNewObject(Resource $resource, ?ManagerInterface $manager = null): object
    {
        if ($manager) {
            $object = $manager->createNew();
        } else {
            $object = new ($resource->getObjectClass());
        }

        return $object;
    }

    protected function getManagerByResource(Resource $resource): ?ManagerInterface
    {
        $manager = null;

        if ($resource->getManagerClass()) {
            $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());
        }

        return $manager;
    }

    /**
     * @throws \Exception
     */
    protected function getDataTransformer(InputDTOInterface $inputDTO, Resource $resource, ?ManagerInterface $manager = null): ?DataTransformerInterface
    {
        $dataTransformer = null;

        if ($resource->getSerializationType() === Resource::TYPE_DATA_TRANSFORMER) {
            $objectClass = $manager ? $manager->getResourceEntityClass() : $resource->getObjectClass();
            if (!$objectClass) {
                throw new \Exception('Object class is missing.');
            }

            $dataTransformer = $this->dataTransformerService->getDataInitializerTransformer($inputDTO, $objectClass, []);
        }

        return $dataTransformer;
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function generateOutputDTO(
        Resource            $resource,
        object              $object,
        ?OutputDTOInterface $outputDTO = null,
                            $deserializationType = null,
        int                 $nestingLevel = 0,
        bool                $isCollectionItem = false
    ): ?OutputDTOInterface {
        //TODO add max level to resource configuration
        if ($nestingLevel >= self::NESTING_LEVEL_MAX || $nestingLevel === self::NESTING_LEVEL_BLOCKED) {
            return null;
        }

        $isIterable = is_iterable($object);
        $processAsCollection = $resource->getIsCollection() && $isIterable;

        $outputDTO = OutputHelper::createNewOutputDTOForService($outputDTO, $resource, $isIterable, $isCollectionItem);

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
                    $this->processPaginationItems($object, $resource, $nestingLevel);
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
                $this->populateDtoWithEntity(targetObject: $object, requestDTO: $outputDTO, resource: $resource, nestingLevel: $nestingLevel + 1);
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
                $this->populateDtoWithEntity(targetObject: $object, requestDTO: $outputDTO, resource: $resource, nestingLevel: $nestingLevel + 1);
            }
        }

        return $outputDTO;
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    protected function processPaginationItems(PaginationInterface $object, Resource $resource, int $nestingLevel): void
    {
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
                $nestingLevel + 1,
                true
            );
        }

        $object->setItems($items);
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
        int                $nestingLevel = 0,
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

        if ($nestingLevel > self::NESTING_LEVEL_MAX) {
            return $inputDTO;
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
                        workflow: DTOService::METHOD_WORKFLOW_INPUT,
                        nestingLevel: $nestingLevel + 1
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

    public function getAppCode(?Request $request = null): ?string
    {
        return $this->appHelper->getAppCode($request);
    }

    public function isGranted(Resource $resource, Request $request, string $action, $subject = null): bool
    {
        return $this->authorizationHelper->isGranted($resource, $request, $action, $subject);
    }

    public function isSubjectGranted(string $managerClass, Request $request, string $action, $subject = null): bool
    {
        return $this->authorizationHelper->isSubjectGranted($managerClass, $request, $action, $subject);
    }

    public function paginateCollection(Resource $resource, Request $request): PaginationInterface
    {
        return $this->collectionHelper->paginateCollection($resource, $request);
    }

    public function checkCollection(Resource $resource, Request $request, iterable $collection, string $actionName): bool
    {
        return $this->collectionHelper->checkCollection($resource, $request, $collection, $actionName);
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
            $objectHolder = null;
            $DTOObjectGetter = null;
            $setterMethod = null;
            $targetValue = null;
            $DTOPropertyName = $reflectionProperty->getName();
            $targetObjectPropertyName = $reflectionProperty->getName();

            $DTOPropertyConfig = self::getDTOPropertyConfig($reflectionProperty);

            if ($DTOPropertyConfig) {
                if ($DTOPropertyConfig->getSkip()) {
                    continue;
                }

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


            if ($convertIdsIntoEntities && $this->hasConvertToObjectAttribute($reflectionProperty) && $dtoValue !== null) {
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
                    if ($reflectionProperty->getType() && $reflectionProperty->getType()->getName() == 'array') {
                        $targetValue = [];
                    } else {
                        $targetValue = null;
                    }
                }
            } else {
                //
                $targetValue = $dtoValue;
            }

            if ($setterMethod) {
                $targetObject->$setterMethod($targetValue);
            } elseif ($propertyAccessor->isWritable($targetObject, $targetObjectPropertyName)) {
                $propertyAccessor->setValue($targetObject, $targetObjectPropertyName, $targetValue);
            }
        }
    }

    /**
     * Support for DTO autofilling for DTO and Entity coexisting properties
     * Used by InputDTO and OutputDTO objects
     *
     * @param object $targetObject
     * @param \LSB\UtilityBundle\DataTransfer\Model\DTOInterface $requestDTO
     * @param \LSB\UtilityBundle\Attribute\Resource|null $resource
     * @param bool $allowNestedObject
     * @param int $workflow
     * @param int $nestingLevel
     * @throws \ReflectionException
     */
    public function populateDtoWithEntity(
        object       $targetObject,
        DTOInterface $requestDTO,
        ?Resource    $resource = null,
        bool         $allowNestedObject = true,
        int          $workflow = self::METHOD_WORKFLOW_OUTPUT,
        int          $nestingLevel = 0

    ): void {
        $propertiesFilter = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE;
        $reflectionDTO = new ReflectionClass($requestDTO);
        $reflectionPropertiesDTO = $reflectionDTO->getProperties($propertiesFilter);

        if ($nestingLevel > self::NESTING_LEVEL_MAX) {
            return;
        }

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
                if ($DTOPropertyConfig->getSkip()) {
                    continue;
                }

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
                $itemResource = $this->resourceHelper->getItemResource($this->getRealClass($value), $reflectionPropertyDTO, true);

                if ($workflow === self::METHOD_WORKFLOW_OUTPUT) {
                    $valueDTO = $this->generateOutputDTO(
                        resource: $itemResource,
                        object: $value,
                        nestingLevel: $nestingLevel + 1
                    );
                } else {

                    if (!$reflectionPropertyDTO->getType()->isBuiltin()) {
                        try {
                            $reflectionClass = new \ReflectionClass($reflectionPropertyDTO->getType()->getName());
                        } catch (\Exception $e) {
                            $reflectionClass = null;
                        }
                    } else {
                        $reflectionClass = null;
                    }

                    //TODO Przygotowanie obsługi zagnieżdzonych InputDTO
                    if ($reflectionPropertyDTO->getType()->getName() === 'string' && $this->hasConvertToObjectAttribute($reflectionPropertyDTO)) {
                        //Dokonujemy próby zamiany obiektu na string
                        $convertToObjectAttribute = $this->getConvertToObjectAttribute($reflectionPropertyDTO);

                        switch ($convertToObjectAttribute->getKey()) {
                            case ConvertToObject::KEY_UUID:
                                $keyPropertyName = 'uuid';
                                break;
                            default:
                                $keyPropertyName = 'id';
                                break;
                        }

                        $propertyAccessor = new PropertyAccessor();

                        if ($propertyAccessor->isReadable($value, $keyPropertyName)) {
                            $valueDTO = $propertyAccessor->getValue($value, $keyPropertyName);
                        } else {
                            $valueDTO = null;
                        }
                    } elseif (!$reflectionPropertyDTO->getType()->isBuiltin()
                        && $reflectionClass
                        && $reflectionClass->implementsInterface(InputDTOInterface::class)) {

                        //Declaring class - klasa która zawiera deklarację tej własności
                        //TODO do sprawdzenia czy nie weryfikacja tej klasy powinna się odbywać na poziomie klasy deklarującej
                        $itemDTO = $this->generateInputDTO(
                            resource: $itemResource,
                            createNewObject: true,
                            nestingLevel: $nestingLevel + 1,
                            isCollectionItem: false,
                            object: $value
                        );

                        $valueDTO = $itemDTO;
                    } else {
                        $valueDTO = null;
                    }
                }

            } elseif (is_iterable($value)) {
                $items = [];

                $itemResource = $this->resourceHelper->getCollectionItemResource($resource, $reflectionPropertyDTO, true);

                if ($workflow === self::METHOD_WORKFLOW_OUTPUT) {
                    foreach ($value as $item) {
                        $itemDTO = $this->generateOutputDTO(
                            resource: $itemResource,
                            object: $item,
                            nestingLevel: $nestingLevel + 1,
                            isCollectionItem: true
                        );
                        $items[] = $itemDTO;
                    }
                } else {
                    foreach ($value as $item) {
                        $itemDTO = $this->generateInputDTO(
                            resource: $itemResource,
                            createNewObject: true,
                            nestingLevel: $nestingLevel + 1,
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
            }
        }
    }

    protected function isStandardObject(object $object): bool
    {
        switch (true) {
            case $object instanceof \DateTime:
            case $object instanceof \StdClass:
            case $object instanceof Money:
            case $object instanceof Value:
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

    protected function getConvertToObjectAttribute(ReflectionProperty $reflectionProperty): ConvertToObject
    {
        $attributes = $reflectionProperty->getAttributes(ConvertToObject::class);

        if (count($attributes) === 0) {
            throw new \Exception(sprintf("ConvertToObject attribute was not found on property: %s", $reflectionProperty->getName()));
        }

        /** @var ConvertToObject $convertToObjectAttribute */
        $convertToObjectAttribute = $attributes[0]->newInstance();

        return $convertToObjectAttribute;
    }

    /**
     * @throws \Exception
     */
    public function convertValueToObjectHolder(
        Request             $request,
        ?ReflectionProperty $reflectionProperty = null,
        ?DTOInterface       $dto = null,
        ?string             $getterName = null,
        ?string             $propertyName = null,
        ?string             $appCode = null,
        int                 $nestingLevel = 0,
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

        if (!$convertToObjectAttribute->getManagerClass()) {
            return null;
        }

        $manager = $this->getManagerContainer()->getByManagerClass($convertToObjectAttribute->getManagerClass());

        if (!$manager instanceof ManagerInterface) {
            throw new \Exception(sprintf("Manager %s was not found.", $convertToObjectAttribute->getManagerClass()));
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
                    request: $request,
                    getterName: $getterName,
                    nestingLevel: $nestingLevel + 1,
                    isCollectionItem: true,
                    convertToObjectAttribute: $convertToObjectAttribute,
                    data: $datum
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
            if ($data && !is_object($data) && (is_string($data) || is_scalar($data)) || $data instanceof Stringable
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
            } elseif ($convertToObjectAttribute->isCreateNewObject()) {
                if ($convertToObjectAttribute->isTranslation()) {
                    $object = $manager->createNewTranslation();
                } else {
                    $object = $manager->createNew();
                }

                if ($data instanceof DTOInterface) {
                    $data->setIsNewObjectCreated(true);
                }
            } else {
                return null;
            }

            //Sprawdzamy czy utworzony obiekt należy zasilić danymi
            if ($data instanceof DTOInterface && $object) {
                $this->populateEntityWithDTO(
                    dto: $data,
                    targetObject: $object,
                    convertIdsIntoEntities: true,
                    request: $request,
                    appCode: $appCode,
                    allowNestedObject: false
                );

                $data->setObject($object);
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

    public function validate(DTOInterface $dto): void
    {
        $this->DTOValidator->validate($dto);
    }

    public function deserialize(Request $request, Resource $resource, ?InputDTOInterface $existingDTO): ?InputDTOInterface
    {
        return $this->DTODeserializer->deserialize($request, $resource, $existingDTO);
    }

    public function serialize($result, Request $request, RequestData $requestData, string|int|null $apiVersionNumeric = null): string
    {
        return $this->DTOSerializer->serialize($result, $request, $requestData, $apiVersionNumeric);
    }

    protected function updateWithManager(?ManagerInterface $manager, object $object): void
    {
        if (!$manager) {
            return;
        }

        $manager->doPersist($object);
        $manager->update($object);
    }
}