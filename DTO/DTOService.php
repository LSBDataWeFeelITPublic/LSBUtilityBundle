<?php

namespace LSB\UtilityBundle\DTO;

use Doctrine\ORM\Proxy\Proxy;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\Controller\BaseApiController;
use LSB\UtilityBundle\DTO\DataTransformer\DataTransformerService;
use LSB\UtilityBundle\DTO\DataTransformer\EntityConverter;
use LSB\UtilityBundle\DTO\Model\Input\InputDTOInterface;
use LSB\UtilityBundle\DTO\Model\Output\OutputDTOInterface;
use LSB\UtilityBundle\DTO\Request\RequestAttributes;
use LSB\UtilityBundle\DTO\Request\RequestIdentifier;
use LSB\UtilityBundle\DTO\Resource\ResourceHelper;
use LSB\UtilityBundle\Interfaces\UuidInterface;
use LSB\UtilityBundle\Repository\PaginationInterface as RepositoryPaginationInterface;
use LSB\UtilityBundle\Service\ManagerContainerInterface;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DTOService
{
    public function __construct(
        protected ManagerContainerInterface     $managerContainer,
        protected DataTransformerService        $dataTransformerService,
        protected AuthorizationCheckerInterface $authorizationChecker,
        protected PaginatorInterface            $paginator,
        protected ResourceHelper                $resourceHelper
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
    public function createNewFromDTO(Resource $resource, $inputDTO, Request $request, ?string $appCode = null): object
    {
        $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());

        if (!$inputDTO->isValid()) {
            throw new \Exception('DTO is invalid. Unable to create new object.');
        }

        $object = $manager->createNew();
        $entityConverter = new EntityConverter();

        if ($resource->getDeserializationType() === Resource::TYPE_DATA_TRANSFORMER) {
            $dataTransformer = $this->dataTransformerService->getDataTransformer($inputDTO, $manager->getResourceEntityClass(), []);
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
            $entityConverter->populateEntityWithDTO($inputDTO, $object, true, $this, $request);
        }

        $manager->update($object);

        return $object;
    }

    /**
     * @param \LSB\UtilityBundle\Attribute\Resource $resource
     * @param $inputDTO
     * @param string|null $appCode
     * @return object
     * @throws \Exception
     */
    public function updateFromDTO(Resource $resource, $inputDTO, Request $request, ?string $appCode = null): object
    {
        $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());

        if (!$inputDTO->isValid()) {
            throw new \Exception('DTO is invalid. Unable to create new object.');
        }

        $object = $inputDTO->getEntity();

        $entityConverter = new EntityConverter();

        if ($resource->getDeserializationType() === Resource::TYPE_DATA_TRANSFORMER) {
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
            $entityConverter->populateEntityWithDTO($inputDTO, $object, true, $this, $request);
        }
        $manager->update($object);

        return $object;
    }

    /**
     * @param \LSB\UtilityBundle\Attribute\Resource $resource
     * @param \LSB\UtilityBundle\DTO\Model\Output\OutputDTOInterface|null $outputDTO
     * @param object $object
     * @param null $deserializationType
     * @param int $level
     * @return \LSB\UtilityBundle\DTO\Model\Output\OutputDTOInterface
     * @throws \Exception
     */
    public function generateOutputDTO(Resource $resource, ?OutputDTOInterface $outputDTO, object $object, $deserializationType = null, int $level = 0): OutputDTOInterface
    {
        $isIterable = is_iterable($object);

        $processAsCollection = $resource->getIsCollection() && $isIterable;

        if ($isIterable) {
            if (!$outputDTO) {
                $outputDTO = new ($resource->getCollectionOutputDTOClass())();
            }
        } elseif (!$outputDTO) {
            $outputDTO = new ($resource->getOutputDTOClass())();
        }

        if (!$deserializationType) {
            $deserializationType = $resource->getDeserializationType();
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
                        $items[] = $this->generateOutputDTO($resource, $resource->getCollectionItemOutputDTOClass(), $item, $resource->getCollectionItemDeserializationType(), $level + 1);
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
                }
            } else {
                $entityConverter = new EntityConverter();
                $entityConverter->populateDtoWithEntity($object, $outputDTO, $this);
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
                }
            } else {
                $entityConverter = new EntityConverter();
                $entityConverter->populateDtoWithEntity($object, $outputDTO, $this);
            }
        }
        return $outputDTO;
    }

    /**
     * @param \LSB\UtilityBundle\Attribute\Resource $resource
     * @param object $object
     * @return \LSB\UtilityBundle\DTO\Model\Output\OutputDTOInterface
     * @throws \Exception
     * @deprecated Draft
     */
    public function generateCollectionItemOutputDTO(Resource $resource, object $object): OutputDTOInterface
    {
        $outputDTO = new ($resource->getCollectionOutputDTOClass())();

        if ($resource->getIsCollection()) {
            //In case of data collection data transformation is required
            if ($resource->getDeserializationType() !== Resource::TYPE_DATA_TRANSFORMER) {
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
            if ($resource->getDeserializationType() === Resource::TYPE_DATA_TRANSFORMER) {
                $dataTransformer = $this->dataTransformerService->getDataTransformer($object, get_class($outputDTO), []);
                if ($dataTransformer) {
                    $outputDTO = $dataTransformer->transform(
                        $object,
                        get_class($outputDTO),
                        $this->dataTransformerService->buildDataTransformerContext(null, $outputDTO, null, null)
                    );
                }
            } else {
                $entityConverter = new EntityConverter();
                $entityConverter->populateDtoWithEntity($object, $outputDTO);
            }
        }


        return $outputDTO;
    }

    /**
     * @param \LSB\UtilityBundle\Attribute\Resource $resource
     * @param \LSB\UtilityBundle\DTO\Request\RequestIdentifier $requestIdentifier
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

        if ($reflectionClass->implementsInterface(UuidInterface::class)
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
    public function prepareInputDTO(
        Resource          $resource,
        RequestIdentifier $requestIdentifier,
        InputDTOInterface $inputDTO,
        bool              $populate = true,
        bool              $createNewObject = false
    ): InputDTOInterface {

        $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());

        $entityClass = $manager->getResourceEntityClass();
        $object = $this->getObjectByRequestId($resource, $requestIdentifier);

        //Verify object class
        if (!$createNewObject && !$object instanceof $entityClass) {
            throw new \Exception('Object does not exist.');
        } elseif ($createNewObject && !$object instanceof $entityClass) {
            $object = $manager->createNew();
        }

        if ($populate) {
            switch ($resource->getDeserializationType()) {
                case Resource::TYPE_AUTO:
                    $entityConverter = new EntityConverter();
                    $entityConverter->populateDtoWithEntity($object, $inputDTO);
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

        $inputDTO->setEntity($object);

        return $inputDTO;
    }

    /**
     * @param \LSB\UtilityBundle\Attribute\Resource $resource
     * @param object $object
     * @param \LSB\UtilityBundle\DTO\Model\Output\OutputDTOInterface $requestDTO
     * @param bool $populate
     * @return \LSB\UtilityBundle\DTO\Model\Output\OutputDTOInterface
     */
    public function prepareOutputDTO(
        Resource           $resource,
        object             $object,
        OutputDTOInterface $requestDTO,
        bool               $populate = true
    ): OutputDTOInterface {

        if ($populate) {
            $entityConverter = new EntityConverter();
            $entityConverter->populateDtoWithEntity($object, $requestDTO);
        }

        //The entity will be used later.
        $requestDTO->setEntity($object);

        return $requestDTO;
    }

    public function getAppCode(Request $request): ?string
    {
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
        $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());
        return $this->authorizationChecker->isGranted($action, $manager->getVoterSubject($subject, $this->getAppCode($request)));
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
     * @param bool $updateConfiguration
     * @return \LSB\UtilityBundle\Attribute\Resource|null
     * @throws \ReflectionException
     */
    public function getResourceByEntity(string $class, bool $updateConfiguration = false): ?Resource
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

        if ($entityResource && $updateConfiguration) {
            $this->resourceHelper->updateResourceConfiguration($entityResource);
        }

        return $entityResource;
    }


}