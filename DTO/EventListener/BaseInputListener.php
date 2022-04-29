<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DTO\EventListener;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\DTO\DataTransformer\DataTransformerService;
use LSB\UtilityBundle\DTO\DTOService;
use LSB\UtilityBundle\DTO\Model\Input\InputDTOInterface;
use LSB\UtilityBundle\DTO\Request\RequestAttributes;
use LSB\UtilityBundle\DTO\Resource\ResourceHelper;
use LSB\UtilityBundle\Manager\ManagerInterface;
use LSB\UtilityBundle\Security\BaseObjectVoter;
use LSB\UtilityBundle\Serializer\ObjectConstructor\ExistingObjectConstructor;
use LSB\UtilityBundle\Service\ApiVersionGrabber;
use LSB\UtilityBundle\Service\ManagerContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class BaseInputListener extends BaseListener
{
    public function __construct(
        protected ManagerContainerInterface     $managerContainer,
        protected ValidatorInterface            $validator,
        protected SerializerInterface           $serializer,
        protected ResourceHelper                $resourceHelper,
        protected DataTransformerService        $dataTransformerService,
        protected DTOService                    $DTOService,
        protected AuthorizationCheckerInterface $authorizationChecker,
        protected ApiVersionGrabber             $apiVersionGrabber
    ) {
    }

    /**
     * @param RequestEvent $requestEvent
     * @return void|null
     * @throws \Exception
     */
    public function onKernelRequest(RequestEvent $requestEvent)
    {
        //Request object
        $request = $requestEvent->getRequest();

        $apiVersion = $this->apiVersionGrabber->getVersion($request, true);

        //in the beginning we create RequestData object
        $requestData = RequestAttributes::getOrderCreateRequestData($request);

        $resource = $this->resourceHelper->fetchResource($request);
        $requestData->setResource($resource);

        if (!$resource instanceof Resource || $resource->getisDisabled() === true) {
            return null;
        }

        //Depending on the request method, we use a different approach to data processing
        if ($resource->getIsCRUD()) {
            if (!$resource->getManagerClass()) {
                throw new \Exception('Manager class is required for CRUD operations.');
            }

            switch ($request->getMethod()) {
                case Request::METHOD_POST:
                    if (!$resource->getIsSecurityCheckDisabled()) {
                        if ($this->DTOService->isGranted($resource, $request, BaseObjectVoter::ACTION_POST, null)) {
                            $requestData->setIsGranted(true);
                        } else {
                            break;
                        }
                    } else {
                        $requestData->setIsGranted(true);
                    }

                    $inputDTO = $this->prepareInputDTO($request, $resource);

                    if (!$resource->getIsActionDisabled() && $inputDTO->isValid()) {
                        $object = $this->DTOService->createNewFromDTO($resource, $inputDTO, $request);
                        $requestData->setObject($object);
                    }

                    $requestData->setInputDTO($inputDTO);
                    break;
                case Request::METHOD_PATCH:
                case Request::METHOD_PUT:
                    $inputDTO = $this->prepareInputDTO($request, $resource);
                    $requestData->setInputDTO($inputDTO);

                    if (!$resource->getIsSecurityCheckDisabled()) {
                        if ($inputDTO->getObject() && $this->DTOService->isGranted($resource, $request, BaseObjectVoter::ACTION_POST, $inputDTO->getObject())) {
                            $requestData->setIsGranted(true);
                        } else {
                            break;
                        }
                    } else {
                        $requestData->setIsGranted(true);
                    }

                    if (!$resource->getIsActionDisabled() && $inputDTO->isValid()) {
                        $object = $this->DTOService->updateFromDTO($resource, $inputDTO, $request, $this->DTOService->getAppCode($request));

                        if (!$requestData->getObject()) {
                            $requestData->setObject($object);
                        }
                    }
                    //$requestData->setInputDTO($inputDTO);
                    break;
                case Request::METHOD_DELETE:
                    if ($resource->getIsActionDisabled()) {
                        break;
                    }

                    if (!$requestData->getObject()) {
                        $object = $this->prepareObject($resource, $request);
                        $requestData->setObject($object);
                    }

                    if (!$resource->getIsSecurityCheckDisabled()) {
                        if ($requestData->getObject() && $this->DTOService->isGranted($resource, $request, BaseObjectVoter::ACTION_DELETE, $requestData->getObject())) {
                            $requestData->setIsGranted(true);
                        } else {
                            break;
                        }
                    } else {
                        $requestData->setIsGranted(true);
                    }

                    $this->DTOService->remove($resource, $requestData->getObject());

                    break;
                case Request::METHOD_GET:
                    if ($resource->getIsActionDisabled()) {
                        break;
                    }

                    //Collection
                    if ($requestData->getResource()->getIsCollection()) {

                        if (!$resource->getIsSecurityCheckDisabled()) {
                            if (!$this->DTOService->isGranted($resource, $request, BaseObjectVoter::ACTION_CGET, $requestData->getObject())) {
                                $requestData->setIsGranted(false);
                                break;
                            }
                        } else {
                            $requestData->setIsGranted(true);
                        }


                        $collection = $this->DTOService->paginateCollection($resource, $request);
                        $requestData->setIsGranted($this->DTOService->checkCollection($resource, $request, $collection, BaseObjectVoter::ACTION_GET));
                        $requestData->setObject($collection);
                    } else {
                        // Single object
                        if (!$requestData->getObject()) {
                            $object = $this->prepareObject($resource, $request);
                            $requestData->setObject($object);
                        }
                        if (!$resource->getIsSecurityCheckDisabled()) {
                            if ($requestData->getObject() && $this->DTOService->isGranted($resource, $request, BaseObjectVoter::ACTION_GET, $requestData->getObject())) {
                                $requestData->setIsGranted(true);
                            } else {
                                break;
                            }
                        } else {
                            $requestData->setIsGranted(true);
                        }
                    }
                    break;
            }
        } else {
            //Non CRUD actions
            //We can convert DTO to some internal object without CRUD actions.
            //Custom logic should be added in controller action body
            switch ($request->getMethod()) {
                case Request::METHOD_POST:
                    $defaultAction = BaseObjectVoter::ACTION_POST;
                    break;
                case Request::METHOD_GET:
                    $defaultAction = BaseObjectVoter::ACTION_GET;
                    break;
                case Request::METHOD_PUT:
                    $defaultAction = BaseObjectVoter::ACTION_PUT;
                    break;
                case Request::METHOD_PATCH:
                    $defaultAction = BaseObjectVoter::ACTION_PATCH;
                    break;
                case Request::METHOD_DELETE:
                    $defaultAction = BaseObjectVoter::ACTION_DELETE;
                    break;
                default:
                    if (!$resource->getVoterAction() && !$resource->getIsSecurityCheckDisabled()) {
                        throw new \Exception('Voter action is required. Disable security check for this action or specify a voter action.');
                    }
                    //Not supported by the listener
                    return;
            }

            if (!$resource->getIsSecurityCheckDisabled()) {
                if ($this->DTOService->isGranted($resource, $request, $resource->getVoterAction() ?? $defaultAction, null)) {
                    $requestData->setIsGranted(true);
                }
            } else {
                $requestData->setIsGranted(true);
            }

            $inputDTO = $this->prepareInputDTO($request, $resource);

            if (!$resource->getIsActionDisabled() && $inputDTO->isValid()) {
                $object = $this->DTOService->createNewFromDTO($resource, $inputDTO, $request);
                $requestData->setObject($object);
            }

            $requestData->setInputDTO($inputDTO);
        }


        RequestAttributes::updateRequestData($request, $requestData);
    }

    /**
     * @param \LSB\UtilityBundle\Attribute\Resource $resource
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return object|null
     * @throws \ReflectionException
     */
    protected function prepareObject(Resource $resource, Request $request): ?object
    {
        return $this->DTOService->getObjectByRequestId($resource, RequestAttributes::getRequestIdentifier($request));
    }

    /**
     * @throws \ReflectionException
     */
    protected function prepareInputDTO(Request $request, Resource $resource): ?InputDTOInterface
    {
        $dto = null;

        if ($resource->getManagerClass()) {
            $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());
        } else {
            $manager = null;
        }

        $context = DeserializationContext::create();

        if ($resource->getIsCRUD() && !$manager instanceof ManagerInterface) {
            throw new \Exception('Manager is required.');
        }

        switch ($request->getMethod()) {
            case Request::METHOD_PATCH:
            case Request::METHOD_PUT:
                if (!$resource->getInputCreateDTOClass()) {
                    throw new \Exception('Input Create DTO Class is missing.');
                }

                //We need to fetch entity with given UUID using class manager
                $dto = new ($resource->getInputUpdateDTOClass())();

                if (!$dto instanceof InputDTOInterface) {
                    throw new \Exception('Input DTO class must implement InputDTOInterface.');
                }

                $requestIdentifier = RequestAttributes::getRequestIdentifier($request);

                if ($resource->getIsCRUD() && !$requestIdentifier) {
                    throw new \Exception('Request identifier is required for PUT & PATCH method. If no entity is used please use POST method.');
                } elseif (!$resource->getIsCRUD() && !$requestIdentifier) {
                    break;
                }

                $dto = $this->DTOService->prepareInputDTO(
                    $resource,
                    $requestIdentifier,
                    $dto,
                    $request->getMethod() === Request::METHOD_PATCH,
                    $request->getMethod() === Request::METHOD_PUT
                );
                $context->setAttribute(ExistingObjectConstructor::ATTRIBUTE_TARGET, $dto);
                break;
        }

        if (!$resource->getInputCreateDTOClass()) {
            throw new \Exception('Input Create DTO Class is missing.');
        }

        $dto = $this->serializer->deserialize(
            $request->getContent(),
            $resource->getInputCreateDTOClass(),
            $request->getFormat($request->headers->get('Content-Type')),
            $context
        );

        /**
         * @var ConstraintViolationList $error
         */
        $errors = $this->validator->validate($dto);

        /**
         * @var ConstraintViolation $error
         */
        foreach ($errors as $error) {
            $dto->addError($error->getPropertyPath(), $error->getMessage());
        }

        return $dto;
    }
}